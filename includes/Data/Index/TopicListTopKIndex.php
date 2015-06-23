<?php

namespace Flow\Data\Index;

use Flow\Model\Workflow;

/**
 * Holds the top K topics in the given sort order.  List is sorted and truncated to specified size.
 */
class TopicListTopKIndex extends TopKIndex {
	public function onAfterInsert( $object, array $new, array $metadata ) {
		if ( $object instanceof Workflow ) {
			$workflow = $object;

			// If we're creating a discussion, the discussion initially has 0
			// topics, so create empty TopKIndex's. (This way, after we add the
			// initial topics, we don't have to do a cache fill from the DB,
			// until the cache expires.).
			if ( $workflow->getType() === 'discussion' ) {
				$indexed = array(
					'topic_list_id' => $workflow->getId(),
				);
				$this->cache->set( $this->cacheKey( $indexed ), array() );
			}
			// Do nothing when topic workflows are inserted

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
