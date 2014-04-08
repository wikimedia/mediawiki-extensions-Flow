<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Model\TopicListEntry;

class WorkflowTopicListListener implements LifecycleHandler {

	public function onAfterInsert( $object, array $new ) {
		$list = Container::get( 'storage' )->find( 'TopicListEntry', array( 'topic_id' => $new['workflow_id'] ) );
		if ( $list ) {
			$entry = reset( $list );
			$row = TopicListEntry::toStorageRow( $entry ) + array( 'workflow_last_update_timestamp' => $new['workflow_last_update_timestamp'] );
			Container::get( 'topic_list.last_updated.index' )->onAfterInsert( $entry, $row );
		}
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
		$list = Container::get( 'storage' )->find( 'TopicListEntry', array( 'topic_id' => $new['workflow_id'] ) );
		if ( $list ) {
			$entry = reset( $list );
			$row = TopicListEntry::toStorageRow( $entry );
			Container::get( 'topic_list.last_updated.index' )->onAfterUpdate( $entry, $row, $row + array( 'workflow_last_update_timestamp' => $new['workflow_last_update_timestamp'] ) );
		}
	}

	public function onAfterLoad( $object, array $row ) {}
	public function onAfterRemove( $object, array $old ) {}

}
