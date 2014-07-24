<?php

namespace Flow\Data;

use DeferredUpdates;

class DeferredInsertLifecycleHandler implements LifecycleHandler {
	public function __construct( LifecycleHandler $nested ) {
		$this->nested = $nested;
	}

	public function onAfterInsert( $object, array $row ) {
		$nested = $this->nested;
		DeferredUpdates::addCallableUpdate( function() use ( $nested, $object, $row ) {
			$nested->onAfterInsert( $object, $row );
		} );
	}

	public function onAfterUpdate( $object, array $old, array $new ) {
	}

	public function onAfterRemove( $object, array $row ) {
	}

	public function onAfterLoad( $object, array $row ) {
	}
}
