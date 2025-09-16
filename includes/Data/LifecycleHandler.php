<?php

namespace Flow\Data;

/**
 * Listeners that receive notifications about the lifecycle of
 * a domain model.
 */
interface LifecycleHandler {

	/**
	 * @param object $object
	 * @param array $old
	 * @return void
	 */
	public function onAfterLoad( $object, array $old );

	/**
	 * @param object $object
	 * @param array $new
	 * @param array $metadata
	 * @return void
	 */
	public function onAfterInsert( $object, array $new, array $metadata );

	/**
	 * @param object $object
	 * @param array $old
	 * @param array $new
	 * @param array $metadata
	 * @return void
	 */
	public function onAfterUpdate( $object, array $old, array $new, array $metadata );

	/**
	 * @param object $object
	 * @param array $old
	 * @param array $metadata
	 * @return void
	 */
	public function onAfterRemove( $object, array $old, array $metadata );

	/**
	 * @return void
	 */
	public function onAfterClear();
}
