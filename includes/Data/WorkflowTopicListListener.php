<?php

namespace Flow\Data;

use Flow\Model\TopicListEntry;

/**
 * Every time an action is performed against something within a workflow
 * the workflow's last_update_timestamp is updated as well.  This listener
 * passes that updated timestamp along to the topic list last updated index
 * so that it can reorder any lists this workflow is in.
 */
class WorkflowTopicListListener implements LifecycleHandler {

	/**
	 * @param ObjectManager
	 */
	protected $topicListStorage;

	/**
	 * @param TopKIndex
	 */
	protected $topicListLastUpdatedIndex;

	/**
	 * @param ObjectManager $topicListStorage
	 * @param TopKIndex $topicListLastUpdatedIndex
	 */
	public function __construct( ObjectManager $topicListStorage, TopKIndex $topicListLastUpdatedIndex ) {
		$this->topicListStorage = $topicListStorage;
		$this->topicListLastUpdatedIndex = $topicListLastUpdatedIndex;
	}

	/**
	 * @var string
	 * @return TopicListEntry|bool
	 */
	protected function getTopicListEntry( $workflowId ) {
		$list = $this->topicListStorage->find( array( 'topic_id' => $workflowId ) );

		// One topic maps to only one topic list now
		if ( $list ) {
			return reset( $list );
		} else {
			return false;
		}
	}

	public function onAfterInsert( $object, array $new, array $metadata ) {
		$entry = $this->getTopicListEntry( $new['workflow_id'] );
		if ( $entry ) {
			$row = array(
					'workflow_last_update_timestamp' => $new['workflow_last_update_timestamp']
				) + TopicListEntry::toStorageRow( $entry );
			$this->topicListLastUpdatedIndex->onAfterInsert( $entry, $row, $metadata );
		}
	}

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$entry = $this->getTopicListEntry( $new['workflow_id'] );
		if ( $entry ) {
			$row = TopicListEntry::toStorageRow( $entry );
			$this->topicListLastUpdatedIndex->onAfterUpdate(
				$entry,
				array(
					'workflow_last_update_timestamp' => $old['workflow_last_update_timestamp']
				) + $row,
				array(
					'workflow_last_update_timestamp' => $new['workflow_last_update_timestamp']
				) + $row,
				$metadata
			);
		}
	}

	public function onAfterLoad( $object, array $row ) {}
	public function onAfterRemove( $object, array $old, array $metadata ) {}
}
