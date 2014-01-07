<?php

namespace Flow;

use Flow\Model\AbstractRevision;
use Closure;
use User;

/**
 * Role based security for revisions based on moderation state
 */
class RevisionActionPermissions {
	/**
	 * @var FlowActions
	 */
	protected $actions = array();

	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @param FlowActions $actions
	 * @param User $user
	 */
	public function __construct( FlowActions $actions, User $user ) {
		$this->user = $user;
		$this->actions = $actions;
	}

	/**
	 * Get the name of all the actions the user is allowed to perform.
	 *
	 * @param AbstractRevision[optional] $revision The revision to check permissions against
	 * @return array Array of action names that are allowed
	 */
	public function getAllowedActions( AbstractRevision $revision = null ) {
		$allowed = array();
		foreach( array_keys( $this->actions->getActions() ) as $action ) {
			if ( $this->isAllowedAny( $revision, $action ) ) {
				$allowed[] = $action;
			}
		}
		return $allowed;
	}

	/**
	 * Check if a user is allowed to perform a certain action.
	 *
	 * @param AbstractRevision[optional] $revision
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed( AbstractRevision $revision = null, $action ) {
		// Users must have the core 'edit' permission to perform any write action in flow
		$performsWrites = $this->actions->getValue( $action, 'performs-writes' );
		if ( $performsWrites && ( !$this->user->isAllowed( 'edit' ) || $this->user->isBlocked() ) ) {
			return false;
		}

		// $revision may be null if the revision has yet to be created
		$moderationState = AbstractRevision::MODERATED_NONE;
		if ( $revision instanceof AbstractRevision ) {
			$moderationState = $revision->getModerationState();
		}
		$permission = $this->actions->getValue( $action, 'permissions', $moderationState );

		// If no permission is defined for this state, then the action is not allowed
		// check if permission is set for this action
		if ( $permission === null ) {
			return false;
		}

		// Some permissions may be more complex to be defined as simple array
		// values, in which case they're a Closure (which will accept
		// AbstractRevision & FlowActionPermissions as arguments)
		if ( $permission instanceof Closure ) {
			$permission = $permission( $revision, $this );
		}

		// check if user is allowed to perform action
		return call_user_func_array(
			array( $this->user, 'isAllowedAny' ),
			(array) $permission
		);
	}

	/**
	 * Check if a user is allowed to perform certain actions.
	 *
	 * @param AbstractRevision[optional] $revision
	 * @param string $action
	 * @param string[optional] $action2 Overloadable to check if either of the provided actions are allowed
	 * @return bool
	 */
	public function isAllowedAny( AbstractRevision $revision = null, $action /* [, $action2 [, ... ]] */ ) {
		$actions = func_get_args();
		// Pull $revision out of the actions list
		array_shift( $actions );
		$allowed = false;

		foreach ( $actions as $action ) {
			$allowed |= $this->isAllowed( $revision, $action );

			// as soon as we've found one that is allowed, break
			if ( $allowed ) {
				break;
			}
		}

		return $allowed;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}
}
