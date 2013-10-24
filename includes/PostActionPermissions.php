<?php

namespace Flow;

use Flow\Model\PostRevision;
use Closure;

/**
 * role based security for posts based on moderation state
 */
class PostActionPermissions {

	public function __construct( $user ) {
		$this->user = $user;

		$this->actions = array(
			'hide-post' => array(
				// Permissions required to perform action. The key is the moderation state
				// of the post to perform the action against. The value is a string or array
				// of user rights that can allow this action.
				PostRevision::MODERATED_NONE => 'flow-hide',
			),

			'delete-post' => array(
				PostRevision::MODERATED_NONE => 'flow-delete',
				PostRevision::MODERATED_HIDDEN => 'flow-delete',
			),

			'censor-post' => array(
				PostRevision::MODERATED_NONE => 'flow-censor',
				PostRevision::MODERATED_HIDDEN => 'flow-censor',
				PostRevision::MODERATED_DELETED => 'flow-censor',
			),

			'restore-post' => array(
				PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-censor' ),
				PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
				PostRevision::MODERATED_CENSORED => 'flow-censor',
			),

			'post-history' => array(
				PostRevision::MODERATED_NONE => '',
				PostRevision::MODERATED_HIDDEN => '',
				PostRevision::MODERATED_DELETED => '',
				PostRevision::MODERATED_CENSORED => 'flow-censor',
			),

			'edit-post' => function( PostRevision $post ) use ( $user ) {
				// no permissions needed for own posts
				return array(
					PostRevision::MODERATED_NONE => $post->isCreator( $user ) ? '' : 'flow-edit-post',
				);
			},

			'view' => array(
				PostRevision::MODERATED_NONE => '',
				PostRevision::MODERATED_HIDDEN => array( 'flow-hide', 'flow-delete', 'flow-censor' ),
				PostRevision::MODERATED_DELETED => array( 'flow-delete', 'flow-censor' ),
				PostRevision::MODERATED_CENSORED => 'flow-censor',
			),
		);
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
		if ( $permissions instanceof Closure ) {
			$permissions = $permissions( $post );
		}
		$state = $post->getModerationState();
		// If no permission is defined for this state, then the action is not allowed
		// check if permission is set for this action
		if ( !isset( $permissions[$state] ) ) {
			return false;
		}

		// check if user is allowed to perform action
		$res = call_user_func_array(
			array( $this->user, 'isAllowedAny' ),
			(array) $permissions[$state]
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

