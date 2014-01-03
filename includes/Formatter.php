<?php

namespace Flow;

use Flow\Model\AbstractRevision;
use Flow\Model\Workflow;
use Flow\Block\AbstractBlock;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Language;
use Html;
use Title;
use User;
use ChangesList;

/**
 * This is a "utility" class that might come in useful to generate
 * some output per Flow entry, e.g. for RecentChanges, Contributions, ...
 * These share a lot of common characteristics (like displaying a date, links to
 * the posts, some description of the action, ...)
 *
 * Just extend from this class to use these common util methods, and make sure
 * to pass the correct parameters to these methods. Basically, you'll need to
 * create a new method that'll accept the objects for your specific
 * implementation (like ChangesList & RecentChange objects for RecentChanges, or
 * ContribsPager and a DB row for Contributions). From those rows, you should be
 * able to derive the objects needed to pass to these utility functions (mainly
 * Workflow, AbstractRevision, Title, User and Language objects) and return the
 * output.
 *
 * For implementation examples, check Flow\RecentChanges\Formatter or
 * Flow\Contributions\Formatter.
 */
abstract class AbstractFormatter {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

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
	 * @var array Array of Workflow objects
	 */
	protected $workflows = array();

	/**
	 * @var array Array of AbstractRevision objects
	 */
	protected $revisions = array();

	/**
	 * @param ManagerGroup $storage
	 * @param FlowActions $actions
	 * @param Templating $templating
	 * @param Language $lang
	 */
	public function __construct( ManagerGroup $storage, FlowActions $actions, Templating $templating ) {
		$this->actions = $actions;
		$this->storage = $storage;
		$this->templating = $templating;

		$this->urlGenerator = $this->templating->getUrlGenerator();
	}

	protected function buildActionLinks( Title $title, $action, UUID $workflowId, UUID $postId = null ) {
		// BC for renamed actions
		$alias = $this->actions->getValue( $action );
		if ( is_string( $alias ) ) {
			// All proper actions return arrays, but aliases return a string
			$action = $alias;
		}
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
	 * @return array Contains [timeAndDate, date, time]
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
			wfMessage( 'flow-link-history' )->text()
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
			wfMessage( 'flow-link-history' )->text()
		);
	}

	public function topicLink( Title $title, UUID $workflowId ) {
		return array(
			$this->urlGenerator->buildUrl(
				$title,
				'view',
				array( 'workflow' => $workflowId->getHex() )
			),
			wfMessage( 'flow-link-topic' )->text()
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
			wfMessage( 'flow-link-post' )->text()
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
	public function getActionDescription( Workflow $workflow, $blockType, AbstractRevision $revision, User $user ) {
		// Build description message, piggybacking on history i18n
		$changeType = $revision->getChangeType();
		$msg = $this->actions->getValue( $changeType, 'history', 'i18n-message' );
		$params = $this->actions->getValue( $changeType, 'history', 'i18n-params' );
		$message = $this->buildMessage( $msg, (array) $params, array(
			$revision,
			$this->templating,
			$user,
			$workflow->getId(),
			$blockType
		) )->parse();

		return \Html::rawElement(
			'span',
			array( 'class' => 'plainlinks' ),
			$message
		);
	}

	/**
	 * @param AbstractRevision $revision
	 * @param AbstractRevision[optional] $previousRevision
	 * @return string|bool Chardiff or false on failure
	 */
	protected function getCharDiff( AbstractRevision $revision, AbstractRevision $previousRevision = null ) {
		$previousContent = '';

		if ( $previousRevision ) {
			$previousContent = $previousRevision->getContentRaw();
		}

		return ChangesList::showCharacterDifference( strlen( $previousContent ), strlen( $revision->getContentRaw() ) );
	}

	/**
	 * Load 1 specific workflow.
	 *
	 * @param UUID $workflowId
	 * @return Workflow|bool Requested workflow or false on failure
	 */
	protected function loadWorkflow( UUID $workflowId ) {
		$results = $this->loadWorkflows( array( $workflowId ) );
		if ( !isset( $results[$workflowId->getHex()] ) ) {
			wfWarn( __METHOD__ . ': Could not load workflow ' . $workflowId->getHex() );
			return false;
		}

		return $results[$workflowId->getHex()];
	}

	/**
	 * Load 1 specific revision.
	 *
	 * @param UUID $revisionId
	 * @param string $revisionType Type of revision to load (e.g. Header, PostRevision)
	 * @return AbstractRevision|bool Requested revision or false on failure
	 */
	protected function loadRevision( UUID $revisionId, $revisionType ) {
		$results = $this->loadRevisions( array( $revisionType => array( $revisionId ) ) );
		if ( !isset( $results[$revisionId->getHex()] ) ) {
			wfWarn( __METHOD__ . ': Could not load workflow ' . $revisionId->getHex() );
			return false;
		}

		return $results[$revisionId->getHex()];
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

	/**
	 * Batch-loads multiple workflows at once (and cached results in object)
	 *
	 * @param array $workflowIds
	 * @return array
	 */
	public function loadWorkflows( array $workflowIds ) {
		$results = array();

		// make sure all ids are UUID objects
		$workflowIds = array_map( array( 'Flow\Model\UUID', 'create' ), $workflowIds );

		foreach ( $workflowIds as $i => $workflowId ) {
			// don't query for workflows already in cache
			if ( isset( $this->workflows[$workflowId->getHex()] ) ) {
				$results[$workflowId->getHex()] = $this->workflows[$workflowId->getHex()];
				unset( $workflowIds[$i] );
			}
		}

		// fetch missing workflows
		$workflows = (array) $this->storage->getMulti( 'Workflow', $workflowIds );
		foreach ( $workflows as $workflow ) {
			$results[$workflow->getId()->getHex()] = $workflow;
		}

		// cache in object
		$this->workflows += $results;

		return $results;
	}

	/**
	 * Batch-loads multiple revisions at once (and cached results in object)
	 *
	 * @param array $revisionIds Multi-dimensional array of revisions to fetch,
	 * where the revision class (e.g. Header, PostRevision) is the key, and an
	 * array of revision ids (UUID objects) is the value
	 * @return array
	 */
	public function loadRevisions( array $revisionIds ) {
		$results = array();

		foreach ( $revisionIds as $class => $ids ) {
			// make sure all ids are UUID objects
			$ids = array_map( array( 'Flow\Model\UUID', 'create' ), $ids );

			foreach ( $ids as $i => $id ) {
				// don't query for revisions already in cache
				if ( isset( $this->revisions[$id->getHex()] ) ) {
					$results[$id->getHex()] = $this->revisions[$id->getHex()];
					unset( $ids[$i] );
				}
			}

			// fetch missing revisions
			$revisions = (array) $this->storage->getMulti( $class, $ids );
			foreach ( $revisions as $revision ) {
				$results[$revision->getRevisionId()->getHex()] = $revision;
			}
		}

		// cache in object
		$this->revisions += $results;

		return $results;
	}
}
