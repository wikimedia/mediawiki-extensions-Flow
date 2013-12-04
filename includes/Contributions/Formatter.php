<?php

namespace Flow\Contributions;

use ContribsPager;
use Flow\Model\AbstractRevision;
use Flow\Model\Workflow;
use Flow\Block\AbstractBlock;
use Flow\Data\ManagerGroup;
use Flow\FlowActions;
use Flow\Model\UUID;
use Flow\Templating;
use Flow\UrlGenerator;
use Flow\WorkflowLoaderFactory;
use Language;
use Html;
use Title;
use User;
use ChangesList;

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
	public function __construct( ManagerGroup $storage, WorkflowLoaderFactory $workflowLoaderFactory, FlowActions $actions, Templating $templating ) {
		$this->actions = $actions;
		$this->storage = $storage;
		$this->workflowLoaderFactory = $workflowLoaderFactory;
		$this->templating = $templating;

		$this->urlGenerator = $this->templating->getUrlGenerator();
	}

	/**
	 * @param ContribsPager $pager
	 * @param stdClass $row
	 * @return string|bool false on failure
	 */
	public function format( ContribsPager $pager, $row ) {
		// Get all necessary objects
		$workflow = $row->workflow;
		$revision = $row->revision;
		$lang = $pager->getLanguage();
		$user = $pager->getUser();
		$title = $workflow->getArticleTitle();

		// Fetch Block object
		$block = $this->loadBlock( $title, $workflow->getId(), $row->blocktype );
		if ( !$block ) {
			return false;
		}

		// Fetch required data
		$charDiff = $this->getCharDiff( $revision );
		$description = $this->getActionDescription( $workflow, $block, $revision, $user );
		$dateFormats = $this->getDateFormats( $revision, $user, $lang );
		$links = $this->buildActionLinks(
			$title,
			$revision->getChangeType(),
			$workflow->getId(),
			method_exists( $revision, 'getPostId' ) ? $revision->getPostId() : null
		);

		// Format timestamp: add link
		$formattedTime = $dateFormats['timeAndDate'];
		if ( $links ) {
			list( $url, $message ) = $links[count( $links ) - 1];
			$formattedTime = Html::element(
				'a',
				array(
					'href' => $url,
					'title' => $message->text()
				),
				htmlspecialchars( $formattedTime )
			);
		} else {
			$links = array();
		}

		// If feedback should be hidden, a special class should be added
		if ( $revision->isModerated() ) {
			$formattedTime = '<span class="history-deleted">' . $formattedTime . '</span>';
		}

		// Format links
		foreach ( $links as &$link ) {
			list( $url, $message ) = $link;
			$link = Html::rawElement(
				'a',
				array(
					'href' => $url,
					'title' => $message->text()
				),
				$message->text()
			);
		}
		$linksContent = $lang->pipeList( $links );
		if ( $linksContent ) {
			$linksContent = wfMessage( 'parentheses' )->rawParams( $linksContent )->text();
		}

		// Put it all together
		return
			$formattedTime . ' ' .
			$linksContent . ' . . ' .
			$charDiff . ' . . ' .
			$description;
	}

	protected function buildActionLinks( Title $title, $action, UUID $workflowId, UUID $postId = null ) {
		$links = array();
		switch( $action ) {
			case 'reply':
				$links[] = $this->topicLink( $title, $workflowId );
				break;

			case 'new-post': // fall through
			case 'edit-post':
				$links[] = $this->topicLink( $title, $workflowId );
				$links[] = $this->postLink( $title, $workflowId, $postId );
				break;

			case 'suppress-post':
			case 'delete-post':
			case 'hide-post':
			case 'restore-post':
				$links[] = $this->topicLink( $title, $workflowId );
				$links[] = $this->postHistoryLink( $title, $workflowId, $postId );
				break;

			case 'suppress-topic':
			case 'delete-topic':
			case 'hide-topic':
			case 'restore-topic':
				$links[] = $this->topicLink( $title, $workflowId );
				$links[] = $this->topicHistoryLink( $title, $workflowId );
				break;

			case 'edit-title':
				$links[] = $this->topicLink( $title, $workflowId );
				// This links to the history of the topic title
				$links[] = $this->postHistoryLink( $title, $workflowId, $postId );
				break;

			case 'create-header': // fall through
			case 'edit-header':
				//$links[] = $this->workflowLink( $title, $workflowId );
				break;

			case null:
				wfWarn( __METHOD__ . ': Flow change has null change type' );
				return false;

			default:
				wfWarn( __METHOD__ . ': Unknown Flow action: ' . $action );
				return false;
		}

		return $links;
	}

	/**
	 * @param AbstractRevision $revision
	 * @param User $user
	 * @param Lang $lang
	 * @return array Contains [timeAndData, date, time]
	 */
	protected function getDateFormats( AbstractRevision $revision, User $user, Language $lang ) {
		// date & time
		$timestamp = $revision->getRevisionId()->getTimestampObj()->getTimestamp( TS_MW );
		$dateFormats = array();
		$dateFormats['timeAndDate'] = $lang->userTimeAndDate( $timestamp, $user );
		$dateFormats['date'] = $lang->userDate( $timestamp, $user );
		$dateFormats['time'] = $lang->userTime( $timestamp, $user );

		return $dateFormats;
	}

	public function topicHistoryLink( Title $title, UUID $workflowId ) {
		return array(
			$this->urlGenerator->buildUrl(
				$title,
				'topic-history',
				array( 'workflow' => $workflowId->getHex() )
			),
			wfMessage( 'flow-link-history' )
		);
	}

	public function postHistoryLink( Title $title, UUID $workflowId, UUID $postId ) {
		return array(
			$this->urlGenerator->buildUrl(
				$title,
				'post-history',
				array(
					'workflow' => $workflowId->getHex(),
					'topic' => array( 'postId' => $postId->getHex() ),
				)
			),
			wfMessage( 'flow-link-history' )
		);
	}

	public function topicLink( Title $title, UUID $workflowId ) {
		return array(
			$this->urlGenerator->buildUrl(
				$title,
				'view',
				array( 'workflow' => $workflowId->getHex() )
			),
			wfMessage( 'flow-link-topic' )
		);
	}

	public function postLink( Title $title, UUID $workflowId, UUID $postId ) {
		return array(
			$this->urlGenerator->buildUrl(
				$title,
				'view',
				array(
					'workflow' => $workflowId->getHex(),
					'topic' => array( 'postId' => $postId->getHex() ),
				)
			),
			wfMessage( 'flow-link-post' )
		);
	}

	protected function workflowLink( Title $title, UUID $workflowId ) {
		list( $linkTitle, $query ) = $this->urlGenerator->buildUrlData(
			$title,
			'view'
		);

		return array(
			$linkTitle->getFullUrl( $query ),
			$linkTitle->getPrefixedText()
		);
	}

	/**
	 * Build textual description for Flow's Contributions entries. These piggy-
	 * back on the i18n messages also used for Flow history, as defined in
	 * FlowActions.
	 *
	 * @param Workflow $workflow
	 * @param AbstractBlock $block
	 * @param AbstractRevision $revision
	 * @param User $user
	 * @return string
	 */
	public function getActionDescription( Workflow $workflow, AbstractBlock $block, AbstractRevision $revision, User $user ) {
		// Build description message, piggybacking on history i18n
		$changeType = $revision->getChangeType();
		$msg = $this->actions->getValue( $changeType, 'history', 'i18n-message' );
		$params = $this->actions->getValue( $changeType, 'history', 'i18n-params' );
		$message = $this->buildMessage( $msg, (array) $params, array(
			$revision,
			$this->templating,
			$user,
			$block
		) )->parse();

		return \Html::rawElement(
			'span',
			array( 'class' => 'plainlinks' ),
			$message
		);
	}

	/**
	 * @param AbstractRevision $revision
	 * @return string|bool Chardiff or false on failure
	 */
	protected function getCharDiff( AbstractRevision $revision) {
		$previousContent = '';

		$previousRevisionId = $revision->getPrevRevisionId();
		if ( $previousRevisionId ) {
			$previousRevision = $this->loadRevision( get_class( $revision ), $previousRevisionId );
			if ( $previousRevision !== false ) {
				$previousContent = $previousRevision->getContentRaw();
			}
		}

		return ChangesList::showCharacterDifference( strlen( $previousContent ), strlen( $revision->getContentRaw() ) );
	}

	/**
	 * @param string $revisionType Storage type (e.g. "PostRevision", "Header")
	 * @param UUID $revisionId
	 * @return AbstractRevision|bool Requested revision or false on failure
	 */
	protected function loadRevision( $revisionType, UUID $revisionId ) {
		$revision = $this->storage->get( $revisionType, $revisionId );

		if ( !$revision ) {
			wfWarn( __METHOD__ . ': Could not load revision ' . $revisionId->getHex() );
			return false;
		}

		return $revision;
	}

	/**
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param string $name Block name (e.g. "topic", "header")
	 * @return AbstractBlock|bool Requested block or false on failure
	 */
	protected function loadBlock( Title $title, UUID $workflowId, $name ) {
		$loader = $this->workflowLoaderFactory
			->createWorkflowLoader( $title, $workflowId );
		$blocks = $loader->createBlocks();

		if ( !isset( $blocks[$name] ) ) {
			wfWarn( __METHOD__ . ': Could not load block ' . $name . ' for workflow ' . $workflowId->getHex() );
			return false;
		}

		return $blocks[$name];
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
