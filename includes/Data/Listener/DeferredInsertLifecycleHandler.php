<?php

namespace Flow\Data\Listener;

use Flow\Data\LifecycleHandler;
use SplQueue;

class DeferredInsertLifecycleHandler implements LifecycleHandler {
	/**
	 * @var SplQueue
	 */
	protected $queue;

	/**
	 * @var LifecycleHandler
	 */
	protected $nested;

	public function __construct( SplQueue $queue, LifecycleHandler $nested ) {
		$this->queue = $queue;
		$this->nested = $nested;
	}

	/** @inheritDoc */
	public function onAfterInsert( $object, array $new, array $metadata ) {
		$nested = $this->nested;
		$this->queue->enqueue( static function () use ( $nested, $object, $new, $metadata ) {
			$nested->onAfterInsert( $object, $new, $metadata );
		} );
	}

	/** @inheritDoc */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
		$this->nested->onAfterUpdate( $object, $old, $new, $metadata );
	}

	/** @inheritDoc */
	public function onAfterRemove( $object, array $old, array $metadata ) {
		$this->nested->onAfterRemove( $object, $old, $metadata );
	}

	/** @inheritDoc */
	public function onAfterLoad( $object, array $old ) {
		$this->nested->onAfterLoad( $object, $old );
	}

	public function onAfterClear() {
		// Not clearing $this->queue, this is not data but actual code
		// that needs to be run.
	}
}
