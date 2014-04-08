<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\TopicListEntry;

class WorkflowTopicListListener implements LifecycleHandler {

	/**
	 * @param ObjectManager
	 */
	protected $topicListStorage;

	/**
	 * @param ObjectManager
	 */
	public function __construct( ObjectManager $topicListStorage ) {
		$this->topicListStorage = $topicListStorage;
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

	public function onAfterInsert( $object, array $new ) {
		$entry = $this->getTopicListEntry( $new['workflow_id'] );
		if ( $entry ) {
			$row = TopicListEntry::toStorageRow( $entry )
				+ array(
					'workflow_last_update_timestamp' => $new['workflow_last_update_timestamp']
				);
			Container::get( 'topic_list.last_updated.index' )->onAfterInsert( $entry, $row );
		}
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		$entry = $this->getTopicListEntry( $new['workflow_id'] );
		if ( $entry ) {
			$row = TopicListEntry::toStorageRow( $entry );
			Container::get( 'topic_list.last_updated.index' )->onAfterUpdate(
				$entry,
				$row,
				$row + array(
					'workflow_last_update_timestamp' => $new['workflow_last_update_timestamp']
				)
			);
		}
	}

	public function onAfterLoad( $object, array $row ) {}
	public function onAfterRemove( $object, array $old ) {}

}
