<?php

namespace Flow\View\History;

use Flow\Model\PostRevision;

/**
 * HistoryBundle is quite similar to HistoryRecord, but accepts an array of
 * PostRevision values. Instead of return the info for a specific revision's
 * action, it will return the action's bundle info.
 */
class HistoryBundle extends HistoryRecord {
	/**
	 * @var History
	 */
	protected $data;

	/**
	 * @param History $bundle
	 */
	public function __construct( array $revisions ) {
		$this->data = $revisions;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return parent::getData();
	}

	/**
	 * @return PostRevision
	 */
	public function getRevision() {
		return $this->data[0];
	}

	/**
	 * @param string $action
	 * @return array|bool Array of action details or false if invalid
	 */
	protected function getActionDetails( $action ) {
		$details = parent::getActionDetails( $action );
		return isset( $details['bundle'] ) ? $details['bundle'] : false;
	}
}
