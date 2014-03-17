<?php

namespace Flow\Data;

use Flow\Model\AbstractRevision;
use Flow\Model\Workflow;
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
