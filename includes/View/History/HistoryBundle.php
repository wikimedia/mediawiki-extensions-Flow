<?php

namespace Flow\View\History;

use Flow\Model\AbstractRevision;
use Flow\Exception\InvalidActionException;

/**
 * HistoryBundle is quite similar to HistoryRecord, but accepts an array of
 * AbstractRevision values. Instead of return the info for a specific revision's
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
	 * @return AbstractRevision
	 */
	public function getRevision() {
		return $this->data[0];
	}

	/**
	 * @param string $action
	 * @return array Array of action details
	 */
	protected function getActionDetails( $action ) {
		$details = $this->getActions()->getValue( $action, 'history', 'bundle' );

		if ( $details === null ) {
			throw new InvalidActionException( "History bundle action '$action' is not defined.", 'invalid-action' );
		}

		return $details;
	}
}
