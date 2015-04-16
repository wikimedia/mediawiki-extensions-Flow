<?php

namespace Flow\Data;

/**
 * Listeners that receive notifications about the lifecycle of
 * a domain model.
 */
interface LifecycleHandler {
	function onAfterLoad( $object, array $old );
	function onAfterInsert( $object, array $new, array $metadata );
	function onAfterUpdate( $object, array $old, array $new, array $metadata );
	function onAfterRemove( $object, array $old, array $metadata );
	function onAfterClear();
}
