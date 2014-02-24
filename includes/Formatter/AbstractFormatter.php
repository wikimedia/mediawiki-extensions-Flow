<?php

namespace Flow\Formatter;

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Exception\DataModelException;
use Flow\Model\UUID;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\UrlGenerator;
use Language;
use Html;
use Message;
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
	 * @var Workflow[] Array of Workflow objects
	 */
	protected $workflows = array();

	/**
	 * @var AbstractRevision[] Array of AbstractRevision objects
	 */
	protected $revisions = array();

	/**
	 * @var RevisionActionPermissions Array of [user id => RevisionActionPermissions object]
	 */
	protected $permissions = array();

	/**
	 * @param ManagerGroup $storage
	 * @param FlowActions $actions
	 * @param Templating $templating
	 */
	public function __construct( ManagerGroup $storage, FlowActions $actions, Templating $templating ) {
		$this->actions = $actions;
		$this->storage = $storage;
		$this->templating = $templating;

		$this->urlGenerator = $this->templating->getUrlGenerator();
	}

	/**
	 * @param User $user
	 * @return RevisionActionPermissions
	 */
	protected function getPermissions( User $user ) {
		if ( $user->getId() && isset( $this->permissions[$user->getId()] ) ) {
			return $this->permissions[$user->getId()];
		}

		$permissions = new RevisionActionPermissions( $this->actions, $user );

		// cache objects per user (will usually be only the person viewing
		// whatever is using this formatter)
		if ( $user->getId() ) {
			$this->permissions[$user->getId()] = $permissions;
		}

		return $permissions;
	}

	/**
	 * @param Title $title
	 * @param string $action
	 * @param UUID $workflowId
	 * @param UUID|null $postId
	 * @return array|false
	 */
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
				$links['topic'] = $this->topicLink( $title, $workflowId );
				if ( $postId ) {
					$links['post'] = $this->postLink( $title, $workflowId, $postId );
				}
				break;

			case 'new-post': // fall through
			case 'edit-post':
				$links['topic'] = $this->topicLink( $title, $workflowId );
				if ( $postId ) {
					$links['post'] = $this->postLink( $title, $workflowId, $postId );
				}
				break;

			case 'suppress-post':
			case 'delete-post':
			case 'hide-post':
			case 'restore-post':
				$links['topic'] = $this->topicLink( $title, $workflowId );
				if ( $postId ) {
					$links['post-history'] = $this->postHistoryLink( $title, $workflowId, $postId );
				}
				break;

			case 'suppress-topic':
			case 'delete-topic':
			case 'hide-topic':
			case 'restore-topic':
				$links['topic'] = $this->topicLink( $title, $workflowId );
				$links['topic-history'] = $this->topicHistoryLink( $title, $workflowId );
				break;

			case 'edit-title':
				$links['topic'] = $this->topicLink( $title, $workflowId );
				// This links to the history of the topic title
				if ( $postId ) {
					$links['title-history'] = $this->postHistoryLink( $title, $workflowId, $postId );
				}
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
	 * @param Language $lang
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
				array( 'workflow' => $workflowId->getAlphadecimal() )
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
					'workflow' => $workflowId->getAlphadecimal(),
					'topic' => array( 'postId' => $postId->getAlphadecimal() ),
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
				array( 'workflow' => $workflowId->getAlphadecimal() )
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
					'workflow' => $workflowId->getAlphadecimal(),
					'topic' => array( 'postId' => $postId->getAlphadecimal() ),
				)
			),
			wfMessage( 'flow-link-post' )
		);
	}

	/**
	 * @param Title $title
	 * @param UUID $workflowId
	 * @param UUID $oldId
	 * @param UUID $newId
	 */
	public function revisionDiffLink( Title $title, UUID $workflowId, UUID $oldId, UUID $newId ) {
		return array(
			$this->urlGenerator->buildUrl(
				$title,
				'compare-revisions',
				array(
					'workflow' => $workflowId->getAlphadecimal(),
					'topic_oldRevision' => $oldId->getAlphadecimal(),
					'topic_newRevision' => $newId->getAlphadecimal(),
				)
			),
			wfMessage( 'diff' )
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
	 * @param string $blockType
	 * @param AbstractRevision $revision
	 * @return string
	 */
	public function getActionDescription( Workflow $workflow, $blockType, AbstractRevision $revision ) {
		// Build description message, piggybacking on history i18n
		$changeType = $revision->getChangeType();
		$msg = $this->actions->getValue( $changeType, 'history', 'i18n-message' );
		$params = $this->actions->getValue( $changeType, 'history', 'i18n-params' );
		$workflowId = $workflow->getId();

		foreach ( $params as &$param ) {
			$param = $this->processParam( $param, $revision, $workflowId, $blockType );
		}

		return \Html::rawElement(
			'span',
			array( 'class' => 'plainlinks' ),
			wfMessage( $msg, $params )->parse()
		);
	}

	/**
	 * @param AbstractRevision $revision
	 * @param AbstractRevision|null $previousRevision
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
		if ( !isset( $results[$workflowId->getAlphadecimal()] ) ) {
			wfWarn( __METHOD__ . ': Could not load workflow ' . $workflowId->getAlphadecimal() );
			return false;
		}

		return $results[$workflowId->getAlphadecimal()];
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
		if ( !isset( $results[$revisionId->getAlphadecimal()] ) ) {
			wfWarn( __METHOD__ . ': Could not load workflow ' . $revisionId->getAlphadecimal() );
			return false;
		}

		return $results[$revisionId->getAlphadecimal()];
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
			if ( isset( $this->workflows[$workflowId->getAlphadecimal()] ) ) {
				$results[$workflowId->getAlphadecimal()] = $this->workflows[$workflowId->getAlphadecimal()];
				unset( $workflowIds[$i] );
			}
		}

		// fetch missing workflows
		$workflows = (array) $this->storage->getMulti( 'Workflow', $workflowIds );
		foreach ( $workflows as $workflow ) {
			$results[$workflow->getId()->getAlphadecimal()] = $workflow;
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
				if ( isset( $this->revisions[$id->getAlphadecimal()] ) ) {
					$results[$id->getAlphadecimal()] = $this->revisions[$id->getAlphadecimal()];
					unset( $ids[$i] );
				}
			}

			// fetch missing revisions
			$revisions = (array) $this->storage->getMulti( $class, $ids );
			foreach ( $revisions as $revision ) {
				$results[$revision->getRevisionId()->getAlphadecimal()] = $revision;
			}
		}

		// cache in object
		$this->revisions += $results;

		return $results;
	}

	/**
	 * Mimic Echo parameter formatting
	 *
	 * @param string $param The requested i18n parameter
	 * @param AbstractRevision|array $revision The revision to format or an array of revisions
	 * @param UUID $workflowId The UUID of the workflow $revision belongs tow
	 * @param string $blockType The type of block $workflowId belongs to
	 * @return mixed A valid parameter for a core Message instance
	 */
	protected function processParam( $param, /* AbstractRevision|array */ $revision, UUID $workflowId, $blockType ) {
		switch ( $param ) {
		case 'creator-text':
			if ( $revision instanceof PostRevision ) {
				return $this->templating->getCreatorText( $revision );
			} else {
				return '';
			}

		case 'user-text':
			return $this->templating->getUserText( $revision );

		case 'user-links':
			return Message::rawParam( $this->templating->getUserLinks( $revision ) );

		case 'summary':
			global $wgLang;
			/*
			 * Fetch in HTML; unparsed wikitext in summary is pointless.
			 * Larger-scale wikis will likely also store content in html, so no
			 * Parsoid roundtrip is needed then (and if it *is*, it'll already
			 * be needed to render Flow discussions, so this is manageable)
			 */
			$content = $this->templating->getContent( $revision, 'html' );
			$content = strip_tags( $content );
			return $wgLang->truncate( trim( $content ), 140 );

		case 'wikitext':
			$content = $this->templating->getContent( $revision, 'wikitext' );
			return Message::rawParam( htmlspecialchars( $content ) );

		case 'prev-wikitext':
			if ( $revision->isFirstRevision() ) {
				return '';
			}
			$previousRevision = $revision->getCollection()->getPrevRevision( $revision );
			if ( !$previousRevision ) {
				// wfDebugLog( __CLASS__, __FUNCTION__ . ': Something something' );
				return '';
			}
			if ( !$this->templating->getActionPermissions()->isAllowed( $previousRevision, 'view' ) ) {
				// @todo message about being unavailable?
				return '';
			}

			$content = $this->templating->getContent( $previousRevision, 'wikitext' );
			return Message::rawParam( htmlspecialchars( $content ) );

		case 'workflow-url':
			return $this->templating->getUrlGenerator()->generateUrl( $workflowId );

		case 'post-url':
			return $this->templating->getUrlGenerator()
				->generateUrl(
					$workflowId,
					'view',
					array(),
					'flow-post-' . $revision->getPostId()->getAlphadecimal()
				);

		case 'moderated-reason':
			// don-t parse wikitext in the moderation reason
			return Message::rawParam( htmlspecialchars( $revision->getModeratedReason() ) );

		case 'topic-of-post':
			try {
				$content = $this->templating->getContent( $revision->getRootPost(), 'wikitext' );
			} catch ( DataModelException $e ) {
				$found = Container::get( 'storage.post' )->find(
					array( 'tree_rev_descendant_id' => $workflowId ),
					array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
				);
				if ( !$found ) {
					wfWarn( __METHOD__ . ': No tree_rev_descendant_id matching ' . $workflowId->getAlphadecimal() );
					return '';
				}
				$content = $this->templating->getContent( reset( $found ), 'wikitext' );
			}
			return Message::rawParam( htmlspecialchars( $content ) );

		case 'bundle-count':
			return array( 'num' => count( $revision ) );

		default:
			wfWarn( __METHOD__ . ': Unknown formatter parameter: ' . $param );
			return '';
		}
	}
}
