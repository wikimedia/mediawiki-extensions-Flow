<?php

namespace Flow\Data\Index;

use Flow\Model\Workflow;

/**
 * Holds the top K topics in the given sort order.  List is sorted and truncated to specified size.
 */
class TopicListTopKIndex extends TopKIndex {
	public function onAfterInsert( $object, array $new, array $metadata ) {
		if ( $object instanceof Workflow ) {
			return;
		}

		parent::onAfterInsert( $object, $new, $metadata );
	}

	// Override the others from LifecycleHandler to do nothing for workflows (unless
	// it's already a no-op in TopKIndex).
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		if ( !( $object instanceof Workflow ) ) {
			parent::onAfterUpdate( $object, $old, $new, $metadata );
		}
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		if ( !( $object instanceof Workflow ) ) {
			parent::onAfterRemove( $object, $old, $metadata );
		}
	}
}
