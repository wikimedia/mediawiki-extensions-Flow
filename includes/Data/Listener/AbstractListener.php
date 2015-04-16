<?php

namespace Flow\Data\Listener;

use Closure;
use Flow\Container;
use Flow\Data\LifecycleHandler;
use Flow\Data\Utils\RecentChangeFactory;
use Flow\FlowActions;
use Flow\Formatter\IRCLineUrlFormatter;
use Flow\Model\AbstractRevision;
use Flow\Model\Workflow;
use Flow\Repository\UserNameBatch;

/**
 * Inserts mw recentchange rows for flow AbstractRevision instances.
 */
class AbstractListener implements LifecycleHandler {

	/**
	 * {@inheritDoc}
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterRemove( $object, array $old, array $metadata ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterLoad( $object, array $row ) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function onAfterInsert( $revision, array $row, array $metadata ) {
	}
}
