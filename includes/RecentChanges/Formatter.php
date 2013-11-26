<?php

namespace Flow\RecentChanges;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\FlowActions;
use Flow\Model\UUID;
use ChangesList;
use Flow\Templating;
use Flow\UrlGenerator;
use Flow\WorkflowLoaderFactory;
use Language;
use Linker;
use Html;
use RecentChange;
use Title;
use User;

class Formatter {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var WorkflowLoaderFactory
	 */
	protected $workflowLoaderFactory;

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @var Templating
	 */
	protected $templating;

	/**
	 * @var UrlGenerator;
	 */
	protected $urlGenerator;

	/**
	 * @var Language
	 */
	protected $lang;

	/**
	 * @param ManagerGroup $storage
	 * @param WorkflowLoaderFactory $workflowLoaderFactory
	 * @param FlowActions $actions
	 * @param Templating $templating
	 * @param Language $lang
	 */
	public function __construct( ManagerGroup $storage, WorkflowLoaderFactory $workflowLoaderFactory, FlowActions $actions, Templating $templating, Language $lang ) {
		$this->actions = $actions;
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
		$this->templating = $templating;
		$this->lang = $lang;

		$this->urlGenerator = $this->templating->getUrlGenerator();
	}

	public function format( ChangesList $cl, RecentChange $rc ) {
		$params = unserialize( $rc->getAttribute( 'rc_params' ) );
		$changeData = $params['flow-workflow-change'];

		if ( !is_array( $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow data missing in recent changes.' );
			return false;
		}

		// used in $this->buildActionLinks()
		if ( !array_key_exists( 'action', $changeData ) ) {
			wfWarn( __METHOD__ . ': Flow action missing' );

			// Backward compatibility; get newer action name based on old type.
			if ( array_key_exists( 'type', $changeData ) ) {
				try {
					$action = $this->actions->getValue( $changeData['type'] );
					if ( is_string( $action ) ) {
						$changeData['action'] = $action;
					} else {
						return false;
					}
				} catch ( \MWException $e ) {
					return false;
				}
			} else {
				return false;
			}
		}

		$line = '';
		$title = $rc->getTitle();
		$links = $this->buildActionLinks( $title, $changeData );

		if ( $links ) {
			$linksContent = $cl->getLanguage()->pipeList( $links );
			$line .= wfMessage( 'parentheses' )->rawParams( $linksContent )->text()
				. $this->changeSeparator();
		}

		$line .= $this->workflowLink( $title, $changeData )
			. wfMessage( 'semicolon-separator' )->text()
			. $this->getTimestamp( $cl, $rc )
			. ' '
			. $this->changeSeparator()
			. ' '
			. $this->getActionDescription( $changeData, $cl, $rc );

		return $line;
	}

	protected function buildActionLinks( Title $title, array $changeData ) {
		$links = array();
		switch( $changeData['action'] ) {
			case 'reply':
				$links[] = $this->topicLink( $title, $changeData );
				break;

			case 'new-post': // fall through
			case 'edit-post':
				$links[] = $this->topicLink( $title, $changeData );
				$links[] = $this->postLink( $title, $changeData );
				break;

			case 'suppress-post':
			case 'delete-post':
			case 'hide-post':
			case 'restore-post':
				$links[] = $this->topicLink( $title, $changeData );
				$links[] = $this->postHistoryLink( $title, $changeData );
				break;

			case 'suppress-topic':
			case 'delete-topic':
			case 'hide-topic':
			case 'restore-topic':
				$links[] = $this->topicLink( $title, $changeData );
				$links[] = $this->topicHistoryLink( $title, $changeData );
				break;

			case 'edit-title':
				$links[] = $this->topicLink( $title, $changeData );
				// This links to the history of the topic title
				$links[] = $this->postHistoryLink( $title, $changeData );
				break;

			case 'create-header': // fall through
			case 'edit-header':
				//$links[] = $this->workflowLink( $title, $changeData );
				break;

			case null:
				wfWarn( __METHOD__ . ': Flow change has null change type' );
				return false;

			default:
				wfWarn( __METHOD__ . ': Unknown Flow action: ' . $changeData['action'] );
				return false;
		}

		return $links;
	}

	protected function changeSeparator() {
		return ' <span class="mw-changeslist-separator">. .</span> ';
	}

	protected function getTimestamp( $cl, $rc ) {
		return '<span class="mw-changeslist-date">'
				. $cl->getLanguage()->userTime( $rc->mAttribs['rc_timestamp'], $cl->getUser() )
			. '</span> ';
	}

	public function topicHistoryLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'topic-history',
					array( 'workflow' => $changeData['workflow'] )
				),
			),
			wfMessage( 'flow-link-history' )->text()
		);
	}

	public function postHistoryLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'post-history',
					array(
						'workflow' => $changeData['workflow'],
						'topic' => array( 'postId' => $changeData['post'] ),
					)
				),
			),
			wfMessage( 'flow-link-history' )->text()
		);
	}

	public function topicLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'view',
					array( 'workflow' => $changeData['workflow'] )
				),
			),
			wfMessage( 'flow-link-topic' )->text()
		);
	}

	public function postLink( Title $title, array $changeData ) {
		return Html::rawElement(
			'a',
			array(
				'href' => $this->urlGenerator->buildUrl(
					$title,
					'view',
					array(
						'workflow' => $changeData['workflow'],
						'topic' => array( 'postId' => $changeData['post'] ),
					)
				),
			),
			wfMessage( 'flow-link-post' )
		);
	}

	protected function workflowLink( Title $title, array $changeData ) {
		list( $linkTitle, $query ) = $this->urlGenerator->buildUrlData(
			$title,
			'view'
		);

		return Html::element(
			'a',
			array( 'href' => $linkTitle->getFullUrl( $query ) ),
			$linkTitle->getPrefixedText()
		);
	}

	/**
	 * Build textual description for Flow's RecentChanges entries. These piggy-
	 * back on the i18n messages also used for Flow history, as defined in
	 * FlowActions.
	 *
	 * @param array $changeData
	 * @param ChangesList $cl
	 * @param RecentChange $rc
	 * @return string
	 */
	public function getActionDescription( array $changeData, ChangesList $cl, RecentChange $rc ) {
		// Fetch Block object
		$title = Title::newFromText( $rc->getAttribute( 'rc_title' ), (int) $rc->getAttribute( 'rc_namespace' ) );
		$block = $this->loadBlock( $title, $changeData['workflow'], $changeData['block'] );
		if ( !$block ) {
			wfWarn( __METHOD__ . ': Could not load block ' . $changeData['block'] . ' for workflow ' . $changeData['workflow'] );
			return '';
		}

		// Fetch requested Revision
		$revision = $this->storage->get( $changeData['revision_type'], UUID::create( $changeData['revision'] ) );
		if ( !$revision ) {
			wfWarn( __METHOD__ . ': Could not load revision ' . $changeData['revision'] );
			return '';
		}

		// Build description message, piggybacking on history i18n
		$msg = $this->actions->getValue( $changeData['action'], 'history', 'i18n-message' );
		$params = $this->actions->getValue( $changeData['action'], 'history', 'i18n-params' );
		$message = $this->buildMessage( $msg, (array) $params, array(
			$revision,
			$this->templating,
			$cl->getUser(),
			$block
		) )->parse();

		return \Html::rawElement(
			'span',
			array( 'class' => 'plainlinks' ),
			$message
		);
	}

	/**
	 * @param Title $title
	 * @param string $definitionId
	 * @param string $workflowId
	 * @param string $name Block name
	 * @return AbstractBlock|false Requested block or false on failure
	 */
	protected function loadBlock( Title $title, $workflowId, $name ) {
		$loader = $this->workflowLoaderFactory
			->createWorkflowLoader( $title, UUID::create( $workflowId ) );
		$blocks = $loader->createBlocks();
		return isset( $blocks[$name] ) ? $blocks[$name] : false;
	}

	/**
	 * Returns i18n message for $msg; piggybacking on History i18n.
	 *
	 * Complex parameters can be injected in the i18n messages. Anything in
	 * $params will be call_user_func'ed, with these given $arguments.
	 * Those results will be used as message parameters.
	 *
	 * Note: return array( 'raw' => $value ) or array( 'num' => $value ) for
	 * raw or numeric parameter input.
	 *
	 * @param string $msg i18n key
	 * @param array[optional] $params Callbacks for parameters
	 * @param array[optional] $arguments Arguments for the callbacks
	 * @return Message
	 */
	protected function buildMessage( $msg, array $params = array(), array $arguments = array() ) {
		foreach ( $params as &$param ) {
			if ( is_callable( $param ) ) {
				$param = call_user_func_array( $param, $arguments );
			}
		}

		return wfMessage( $msg, $params );
	}
}
