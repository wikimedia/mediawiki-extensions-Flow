<?php

namespace Flow;

use Flow\Model\PostRevision;
use Closure;
use User;

/**
 * role based security for posts based on moderation state
 */
class PostActionPermissions {
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
	 * @param PostRevision $post The post to check permissions against
	 * @return array Array of action names that are allowed
	 */
	public function getAllowedActions( PostRevision $post ) {
		$allowed = array();
		foreach( array_keys( $this->actions->getActions() ) as $action ) {
			if ( $this->isAllowedAny( $post, $action ) ) {
				$allowed[] = $action;
			}
		}
		return $allowed;
	}

	/**
	 * Check if a user is allowed to perform a certain action.
	 *
	 * @param PostRevision $post
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed( PostRevision $post, $action ) {
		// Users must have the core 'edit' permission to perform any write action in flow
		$performsWrites = $this->actions->getValue( $action, 'performs-writes' );
		if ( $performsWrites && !$this->user->isAllowed( 'edit' ) ) {
			return false;
		}
		$permission = $this->actions->getValue( $action, 'permissions', $post->getModerationState() );

		// If no permission is defined for this state, then the action is not allowed
		// check if permission is set for this action
		if ( $permission === null ) {
			return false;
		}

		// Some permissions may be more complex to be defined as simple array
		// values, in which case they're a Closure (which will accept
		// PostRevision & PostActionPermissions as arguments)
		if ( $permission instanceof Closure ) {
			$permission = $permission( $post, $this );
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
	 * @param PostRevision $post
	 * @param string $action
	 * @param string[optional] $action2 Overloadable to check if either of the provided actions are allowed
	 * @return bool
	 */
	public function isAllowedAny( PostRevision $post, $action /* [, $action2 [, ... ]] */ ) {
		$actions = func_get_args();
		// Pull $post out of the actions list
		array_shift( $actions );
		$allowed = false;

		foreach ( $actions as $action ) {
			$allowed |= $this->isAllowed( $post, $action );

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
