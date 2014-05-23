<?php

namespace Flow\Data\RevisionState;

use Flow\Model\AbstractRevision;

/**
 * Entity for checking the status of a revision
 */
class RevisionStateChecker {

	/**
	 * @var RevisionState[]
	 */
	protected $states;

	/**
	 * @param AbstractRevision
	 */
	public function __construct( AbstractRevision $revision ) {
		$this->states = $this->revision->getRevisionState();
	}

	/**
	 * Check if a state is set, this is meant to replace: isHidden(), isClosed() etc
	 * @param string Any of the state constant from RevisionStateValue
	 * @return boolean
	 */
	public function isStateSet( $state ) {
		$states = $this->revision->getRevisionState();
		return isset( $states[$state] );
	}

	/**
	 * Check if the revision content is set with a state
	 */
	public function isContentSetWithState() {
		foreach ( $this->states as $state => $row ) {
			if ( $state !== RevisionStateValue::None ) {
				$context = RevisionStateValue::$context[$state];
				if (
					 $context === 'revision' ||
					 $context === 'content'
				) {
					return true;
				}
			}
		}
		return false;
	}

}
