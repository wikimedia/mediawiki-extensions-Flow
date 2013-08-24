<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Repository\TreeRepository;

class PostRevisionRecentChanges implements LifecycleHandler {
	public function __construct( ManagerGroup $storage, TreeRepository $tree ) {
		$this->storage = $storage;
		$this->tree = $tree;
	}

	public function onAfterInsert( $object, array $row ) {
		// There might be a more efficient way to get this workflow id
		$workflowId = $this->tree->findRoot( $object->getPostId() );
		if ( !$workflowId ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ": could not locate root for post " . $object->getPostId()->getHex() );
			return;
		}
		// These are likely already in the in-process cache
		$workflow = $this->storage->get( 'Workflow', $workflowId );
		if ( !$workflow ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ": could not locate workflow $workflowId" );
			return;
		}

		$isFirstRev = !$object->getPrevRevisionId();
		if ( $object->isTopicTitle() ) {
			$type = $object->isFirstRevision() ? 'new-topic' : 'edit-topic-title';
		} elseif ( $object->isFirstRevision()) {
			// Everything except the topic title is guarantee'd to be a reply
			if ( $object->getReplyToId()->equals( $workflowId ) ) {
				// this is the first post, which is already in recent changes through the new-topic
				return;
			} else {
				$type = 'new-post';
			}
		} else {
			// how to determine what happened and needs to be logged?
			// could be moderation, or content edit
			$type = 'moderate-post';
		}

		$this->insert(
			$type,
			$row,
			$workflow,
			$object->getRevisionId(),
			array(
				'post' => $object->getPostId()->getHex(),
				'revision' => $object->getRevisionId()->getHex(),
				'comment' => $object->getComment(),
			)
		);
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		// nothing to do
	}

	public function onAfterRemove( $object, array $old ) {
		// nothing to do
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

