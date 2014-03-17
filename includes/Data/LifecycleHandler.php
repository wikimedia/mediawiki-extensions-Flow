<?php

namespace Flow\Data;

/**
 * Listeners that receive notifications about the lifecycle of
 * a domain model.
 */
interface LifecycleHandler {
	function onAfterLoad( $object, array $old );
	function onAfterInsert( $object, array $new );
	function onAfterUpdate( $object, array $old, array $new );
	function onAfterRemove( $object, array $old );
}
