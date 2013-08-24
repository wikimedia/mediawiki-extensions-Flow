<?php

namespace Flow\Data;

use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Repository\TreeRepository;

abstract class RecentChanges implements LifecycleHandler {

	public function onAfterInsert( $object, array $row ) {
		// New Revision
		throw new \MWException( 'onAfterInsert must be implemented' );
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

	protected function insert( $type, array $row, Workflow $workflow, $timestamp, array $changes ) {
		if ( $timestamp instanceof UUID ) {
			$timestamp = $timestamp->getTimestamp();
		}
		$title = $workflow->getTitle();

		$attribs = array(
			'rc_namespace' => $title->getNamespace(),
			'rc_title' => $title->getDBkey(),
			'rc_user' => $row['rev_user_id'],
			'rc_user_text' => $row['rev_user_text'],
			'rc_type' => RC_FLOW,
			// 'rc_source' => RC_SRC_FLOW, // depends on core change in gerrit 85787
			'rc_minor' => 0,
			'rc_bot' => 0, // TODO: is revision by bot
			'rc_patrolled' => 0,
			'rc_old_len' => 0,
			'rc_new_len' => 0,
			'rc_this_oldid' => 0,
			'rc_last_oldid' => 0,
			'rc_params' => serialize( array(
				'flow-workflow-change' => array(
					'type' => $type,
					'workflow' => $workflow->getId()->getHex(),
					'definition' => $workflow->getDefinitionId()->getHex(),
				) + $changes,
			) ),
			'rc_cur_id' => 0, // TODO: wtf do we do with uuid's?
			'rc_comment' => '',
			'rc_timestamp' => $timestamp,
			'rc_cur_time' => $timestamp,
		);

		$dbw = wfGetDB( DB_MASTER );
		return $dbw->insert( 'recentchanges', $attribs, __METHOD__ );
	}
}

class SummaryRecentChanges extends RecentChanges {
	public function __construct( ManagerGroup $storage ) {
		$this->storage = $storage;
	}

	public function onAfterInsert( $object, array $row ) {
		$workflowId = $object->getWorkflowId();
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ": could not locate workflow for summary " . $object->getRevisionId()->getHex() );
			return;
		}

		$this->insert(
			$object->getComment(),
			$row,
			$workflow,
			$object->getRevisionId(),
			array(
				'revision' => $object->getRevisionId()->getHex(),
				'content' => $object->getContent(),
			)
		);
	}
}

class PostRevisionRecentChanges extends RecentChanges {
	public function __construct( ManagerGroup $storage, TreeRepository $tree ) {
		$this->storage = $storage;
		$this->tree = $tree;
	}

	public function onAfterInsert( $object, array $row ) {
		// There might be a more efficient way to get this workflow id
		$workflowId = $this->tree->findRoot( $object->getPostId() );
		if ( !$workflowId ) {
			wfWarn( __METHOD__ . ": could not locate root for post " . $object->getPostId()->getHex() );
			return;
		}
		// These are likely already in the in-process cache
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			wfWarn( __METHOD__ . ": could not locate workflow $workflowId" );
			return;
		}

		$this->insert(
			$object->getComent(),
			$row,
			$workflow,
			$object->getRevisionId(),
			$params,
			array(
				'post' => $object->getPostId()->getHex(),
				'revision' => $object->getRevisionId()->getHex(),
				'topic' => $this->getTopicTitle( $object ),
			)
		);
	}

	protected function getTopicTitle( PostRevision $rev ) {
		if ( $rev->isTopicTitle() ) {
			return $rev->getContent();
		} else {
			$found = $this->storage->find(
				'PostRevision',
				array( 'tree_rev_descendant_id' => $workflowId ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( $found ) {
				return reset( $found )->getContent();
			}
		}
		return null;
	}
}
