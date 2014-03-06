<?php

namespace Flow\Data;

use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Repository\TreeRepository;
use Language;
use RecentChange;

abstract class RecentChanges implements LifecycleHandler {

	// Value used in rc_source field of recentchanges to identify flow specific changes
	const SRC_FLOW = "flow";

	// Maximum length any user generated content is truncated to before storing
	// in recentchanges
	const TRUNCATE_LENGTH = 164;

	/**
	 * @var UserNameBatch
	 */
	protected $usernames;

	/**
	 * @param UserNameBatch $usernames
	 */
	public function __construct( UserNameBatch $usernames ) {
		$this->usernames = $usernames;
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		// Moderation.  Doesn't need to log anything because all moderation also inserts
		// a new null revision to track who and when.
	}

	public function onAfterRemove( $object, array $old ) {
		// Deletion. Not kinda-sorta deleted, like 100% GONE. Should never happen.
	}

	public function onAfterLoad( $object, array $row ) {
		// nothing to do
	}

	/**
	 * @param AbstractRevision $revision Revision object
	 * @param string $block The block object's ->getName()
	 * @param string $revisionType Classname of the Revision object
	 * @param array $row Revision row
	 * @param Workflow $workflow
	 * @param array $changes
	 */
	protected function insert( AbstractRevision $revision, $block, $revisionType, array $row, Workflow $workflow, array $changes ) {
		$action = $revision->getChangeType();
		$revisionId = $revision->getRevisionId()->getAlphadecimal();
		$timestamp = $revision->getRevisionId()->getTimestamp();

		if ( $action === 'suppress-topic' || $action === 'suppress-post' ) {
			// @todo: should be move this into FlowActions.php somehow?
			// Suppression log entries should not go to recentchanges (bug 60814)
			// @todo: does this still make sense in here? Formatter properly checks permissions now
			return;
		}

		$title = $workflow->getArticleTitle();
		$collection = $revision->getCollection();

		// get content of both this & the current revision
		$content = $revision->getContent( 'wikitext' );
		$previousContent = '';
		$previousRevision = $collection->getPrevRevision( $revision );
		if ( $previousRevision ) {
			$previousContent = $previousRevision->getContent( 'wikitext' );
		}

		$attribs = array(
			'rc_namespace' => $title->getNamespace(),
			'rc_title' => $title->getDBkey(),
			'rc_user' => $row['rev_user_id'],
			'rc_user_text' => $this->usernames->get( wfWikiId(), $row['rev_user_id'], $row['rev_user_ip'] ),
			'rc_type' => RC_FLOW,
			'rc_source' => self::SRC_FLOW, // depends on core change in gerrit 85787
			'rc_minor' => 0,
			'rc_bot' => 0, // TODO: is revision by bot
			'rc_patrolled' => 0,
			'rc_old_len' => strlen( $previousContent ),
			'rc_new_len' => strlen( $content ),
			'rc_this_oldid' => 0,
			'rc_last_oldid' => 0,
			'rc_log_type' => null,
			'rc_params' => serialize( array(
				'flow-workflow-change' => array(
					'action' => $action,
					'block' => $block,
					'revision_type' => $revisionType,
					'revision' => $revisionId,
					'workflow' => $workflow->getId()->getAlphadecimal(),
					'definition' => $workflow->getDefinitionId()->getAlphadecimal(),
				) + $changes,
			) ),
			'rc_cur_id' => 0,
			'rc_comment' => '',
			'rc_timestamp' => $timestamp,
			'rc_cur_time' => $timestamp,
			'rc_deleted' => 0,
		);

		$rc = RecentChange::newFromRow( (object)$attribs );
		$rc->save();  // Insert into db and send to RC feeds
	}
}

class HeaderRecentChanges extends RecentChanges {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var Language Content Language
	 */
	protected $contLang;

	public function __construct( UserNameBatch $usernames, ManagerGroup $storage, Language $contLang ) {
		parent::__construct( $usernames );
		$this->storage = $storage;
		$this->contLang = $contLang;
	}

	/**
	 * @param Header $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row ) {
		$workflowId = $object->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			// unless in unit test, write to log
			wfDebugLog( __CLASS__, __FUNCTION__ . ": could not locate workflow for header " . $object->getRevisionId()->getAlphadecimal() );
			return;
		}

		$this->insert(
			$object,
			'header',
			'Header',
			$row,
			$workflow,
			array(
				'content' => $this->contLang->truncate( $object->getContent(), self::TRUNCATE_LENGTH ),
			)
		);
	}
}

class PostRevisionRecentChanges extends RecentChanges {
	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var TreeRepository
	 */
	protected $tree;

	/**
	 * @var Language
	 */
	protected $contLang;

	public function __construct( UserNameBatch $usernames, ManagerGroup $storage, TreeRepository $tree, Language $contLang ) {
		parent::__construct( $usernames );
		$this->storage = $storage;
		$this->tree = $tree;
		$this->contLang = $contLang;
	}

	/**
	 * @param PostRevision $object
	 * @param string[] $row
	 */
	public function onAfterInsert( $object, array $row ) {
		// The workflow id is the same as the root's post id
		$workflowId = $object->getRootPost()->getPostId();
		// These are likely already in the in-process cache
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			// unless in unit test, write to log
			wfDebugLog( __CLASS__, __FUNCTION__ . ": could not locate workflow " . $workflowId->getAlphadecimal() );
			return;
		}

		$this->insert(
			$object,
			'topic',
			'PostRevision',
			$row,
			$workflow,
			array(
				'post' => $object->getPostId()->getAlphadecimal(),
				'topic' => $this->getTopicTitle( $object ),
			)
		);
	}

	protected function getTopicTitle( PostRevision $rev ) {
		$content = $rev->getRootPost()->getContent( 'wikitext' );
		if ( is_object( $content ) ) {
			// moderated
			return null;
		}

		return $this->contLang->truncate( $content, self::TRUNCATE_LENGTH );
	}
}
