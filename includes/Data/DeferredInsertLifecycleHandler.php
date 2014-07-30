<?php

namespace Flow\Data;

use DeferredUpdates;

class DeferredInsertLifecycleHandler implements LifecycleHandler {
	public function __construct( LifecycleHandler $nested ) {
		$this->nested = $nested;
	}

	public function onAfterInsert( $object, array $new, array $metadata ) {
		$nested = $this->nested;
		DeferredUpdates::addCallableUpdate( function() use ( $nested, $object, $new, $metadata ) {
			$nested->onAfterInsert( $object, $new, $metadata );
		} );
	}

	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$this->nested->onAfterUpdate( $object, $old, $new, $metadata );
	}

	public function onAfterRemove( $object, array $old, array $metadata ) {
		$this->nested->onAfterRemove( $object, $old, $metadata );
	}

	public function onAfterLoad( $object, array $old ) {
		$this->nested->onAfterLoad( $object, $old );
	}
}
