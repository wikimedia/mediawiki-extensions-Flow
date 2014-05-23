<?php

namespace Flow\Data\RevisionState;

use Flow\Exception\InvalidInputException;
use Flow\Model\AbstractRevision;
use Flow\Model\RevisionState;
use User;

/**
 * The entity for modifying a flow revision's state, eg: suppress, close, hide
 */
class RevisionStateModifier {

	/**
	 * @var AbstractRevision
	 */
	protected $revision;

	/**
	 * @var RevisionState[]
	 */
	protected $states;

	/**
	 * @param AbstractRevision
	 */
	public function __construct( AbstractRevision $revision ) {
		$this->revision = $revision;
		$this->states = $this->revision->getRevisionState();
	}

	/**
	 * Moderate a flow revision, eg: suppress/delete.  This is done from the
	 * history page and would not create a new flow revision.  The action is
	 * saved to general logging table
	 *
	 * @param User The user moderating the revision
	 * @param string[] The new moderation states
	 * @param string The moderation comment
	 * @return AbstractRevision
	 */
	public function moderate( User $user, array $newStates, $comment ) {
		foreach ( $newStates as $newState ) {
			if ( !in_array( $newState, RevisionStateValue::$moderateState ) ) {
				throw new InvalidInputException( $newState . ' is not a valid moderation state', 'invalid-input' );
			}
		}

		// If all revision states are set already, there is not point to
		// set it again, just return the revision
		if ( !array_diff( array_keys( $this->states ), $newStates ) ) {
			return $this->revision;
		}

		foreach ( RevisionStateValue::$moderateState as $state ) {
			$this->removeState( $state );
		}

		// Moderation always passes in the new set of states
		foreach ( $newStates as $newState ) {
			$this->addState( RevisionState::create( $user, $newState, $comment ) );
		}
		$this->commitStateToRevision();

		return $this->revision;
	}

	/**
	 * Flag a flow revision, eg: close/hide.  This is done from the board page
	 * and would create a new flow revision.  The action is saved to flow history
	 *
	 * @param User The user flagging the revision
	 * @param strig The new state
	 * @param string The action
	 * @param string comment
	 *
	 * @return AbstractRevision
	 */
	public function flag( User $user, $state, $action, $comment ) {
		$availableStates = RevisionStateValue::$flagState + array_map(
			function( $name ) {
				return 'restore-' . $name;
			},
			RevisionStateValue::$flagState
		);

		if ( !in_array( $state, $availableStates ) ) {
			throw new InvalidInputException( $state . ' is not a valid moderation state', 'invalid-input' );
		}

		$newRevision = $this->revision->newNullRevision( $user );
		$newRevision->setChangeType( $action );

		if ( substr( $state, 0, 8 ) === 'restore-' ) {
			$this->removeState( substr( $state, 8 ) );
		} else {
			$this->addState( RevisionState::create( $user, $state, $coment ) );
		}
		$this->commitStateToRevision( $newRevision );

		return $newRevision;
	}

	/**
	 * Add a revision state to the current revision
	 * @param RevisionState
	 */
	public function addState( RevisionState $newState ) {
		$newStateContext = RevisionStateValue::$content[$newState->getState()];

		// Unset any existing revision state with the same context
		foreach ( $this->states as $name => $row ) {
			if ( RevisionStateValue::$content[$name] === $newStateContext ) {
				unset( $states[$name] );
			}
		}
		$this->states[$row->getState()] = $newState;
	}

	/**
	 * Remove a revision state
	 */
	public function removeState( $state ) {
		unset( $this->states[$state] );
	}

	/**
	 * Save the states to current revision or the revision specified
	 */
	public function commitStateToRevision( AbstractRevision $revision = null ) {
		if ( $revision == null ) {
			$revision = $this->revision;
		}
		$revision->setRevisionState( $this->states );
	}
}
