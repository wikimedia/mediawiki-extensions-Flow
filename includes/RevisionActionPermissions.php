<?php

namespace Flow;

use Flow\Collection\CollectionCache;
use Flow\Exception\InvalidDataException;
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
	 * @param AbstractRevision|null $revision The revision to check permissions against
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
	 * @param AbstractRevision|null $revision
	 * @param string $action
	 * @return bool
	 */
	public function isAllowed( AbstractRevision $revision = null, $action ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		$allowed = $this->isRevisionAllowed( $revision, $action );

		// if there was no revision object, it's pointless to find last revision
		// if we already fail, no need in checking most recent revision status
		if ( $allowed && $revision !== null  ) {
			try {
				// Also check if the user would be allowed to perform this against
				// against the most recent revision - the last revision is the
				// current state of an object, so checking against a revision at one
				// point in time alone isn't enough.
				/** @var CollectionCache $cache */
				$cache = Container::get( 'collection.cache' );
				$last = $cache->getLastRevisionFor( $revision );
				$isLastRevision = $last->getRevisionId()->equals( $revision->getRevisionId() );
				$allowed = $isLastRevision || $this->isRevisionAllowed( $last, $action );
			} catch ( InvalidDataException $e ) {
				// If data is not in storage, just return that revision's status
			}
		}
		return $allowed;
	}

	/**
	 * Check if a user is allowed to perform certain actions.
	 *
	 * @param AbstractRevision|null $revision
	 * @param string $action... Multiple parameters to check if either of the provided actions are allowed
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

	// TODO (mattflaschen, 2014-06-25): Can more of this be removed
	// given the checks in WorkflowLoader and https://gerrit.wikimedia.org/r/#/c/142148/ ?
	/**
	 * Check if a user is allowed to perform a certain action, only against 1
	 * specific revision (whereas the default isAllowed() will check if the
	 * given $action is allowed for both given and the most current revision)
	 *
	 * @param AbstractRevision|null $revision
	 * @param string $action
	 * @return bool
	 */
	protected function isRevisionAllowed( AbstractRevision $revision = null, $action ) {
		// Users must have the core 'edit' permission to perform any write action in flow
		$performsWrites = $this->actions->getValue( $action, 'performs-writes' );
		if ( $performsWrites && !$this->user->isAllowed( 'edit' ) ) {
			return false;
		}

		$permission = $this->getPermission( $revision, $action );

		// If no permission is defined for this state, then the action is not allowed
		// check if permission is set for this action
		if ( $permission === null ) {
			return false;
		}

		// Check if user is allowed to perform action against this revision
		return call_user_func_array(
			array( $this->user, 'isAllowedAny' ),
			(array) $permission
		);
	}

	/**
	 * Returns the permission specified in FlowActions for the given action
	 * against the given revision's moderation state.
	 *
	 * @param AbstractRevision|null $revision
	 * @param string $action
	 * @return Closure|string
	 */
	public function getPermission( AbstractRevision $revision = null, $action ) {
		// $revision may be null if the revision has yet to be created
		$moderationState = AbstractRevision::MODERATED_NONE;
		if ( $revision !== null ) {
			$moderationState = $revision->getModerationState();
		}
		$permission = $this->actions->getValue( $action, 'permissions', $moderationState );

		// Some permissions may be more complex to be defined as simple array
		// values, in which case they're a Closure (which will accept
		// AbstractRevision & FlowActionPermissions as arguments)
		if ( $permission instanceof Closure ) {
			$permission = $permission( $revision, $this );
		}

		return $permission;
	}

	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return FlowActions
	 */
	public function getActions() {
		return $this->actions;
	}
}
