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
	 * @var array
	 */
	protected $actions = array();

	/**
	 * @var User
	 */
	public $user;

	public function __construct( $user ) {
		$this->user = $user;

		global $wgFlowActions;
		$this->actions = array_map( function( $action ) {
			return $action['permissions'];
		}, $wgFlowActions );
	}

	/**
	 * Get the name of all the actions the user is allowed to perform.
	 *
	 * @param PostRevision $post The post to check permissions against
	 * @return array Array of action names that are allowed
	 */
	public function getAllowedActions( PostRevision $post ) {
		$allowed = array();
		foreach( array_keys( $this->actions ) as $action ) {
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
		if ( !isset( $this->actions[$action] ) ) {
			return false;
		}

		$permissions = $this->actions[$action];
		$state = $post->getModerationState();
		// If no permission is defined for this state, then the action is not allowed
		// check if permission is set for this action
		if ( !isset( $permissions[$state] ) ) {
			return false;
		}

		// Some permissions may be more complex to be defined as simple array
		// values, in which case they're a Closure (which will accept
		// PostRevision & PostActionPermissions as arguments)
		$permission = $permissions[$state];
		if ( $permission instanceof Closure ) {
			$permission = $permission( $post, $this );
		}

		// check if user is allowed to perform action
		$res = call_user_func_array(
			array( $this->user, 'isAllowedAny' ),
			(array) $permission
		);
		return $res;
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
}
