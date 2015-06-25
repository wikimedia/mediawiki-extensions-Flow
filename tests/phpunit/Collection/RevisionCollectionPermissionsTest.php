<?php

namespace Flow\Tests\Collection;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\Model\AbstractRevision;
use Flow\RevisionActionPermissions;
use Flow\Tests\PostRevisionTestCase;
use Block;
use User;

/**
 * @group Database
 * @group Flow
 */
class RevisionCollectionPermissionsTest extends PostRevisionTestCase {
	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * Map of action name to moderation status, as helper for
	 * $this->generateRevision()
	 *
	 * @var array
	 */
	protected $moderation = array(
		'restore-post' => AbstractRevision::MODERATED_NONE,
		'hide-post' => AbstractRevision::MODERATED_HIDDEN,
		'delete-post' => AbstractRevision::MODERATED_DELETED,
		'suppress-post' => AbstractRevision::MODERATED_SUPPRESSED,
	);

	/**
	 * @var User
	 */
	protected
		$blockedUser,
		$anonUser,
		$unconfirmedUser,
		$confirmedUser,
		$sysopUser,
		$oversightUser;

	/**
	 * @var Block
	 */
	protected $block;

	protected function setUp() {
		parent::setUp();

		$this->clearExtraLifecycleHandlers();

		// We don't want local config getting in the way of testing whether or
		// not our permissions implementation works well.
		// This will load default $wgGroupPermissions + Flow settings, so we can
		// test if permissions work well, regardless of any custom config.
		global $IP, $wgFlowGroupPermissions;
		$wgGroupPermissions = array();
		require "$IP/includes/DefaultSettings.php";
		$wgGroupPermissions = array_merge_recursive( $wgGroupPermissions, $wgFlowGroupPermissions );
		$this->setMwGlobals( 'wgGroupPermissions', $wgGroupPermissions );

		// When external store is used, data is written to "blobs" table, which
		// by default doesn't exist - let's just not use externalstorage in test
		$this->setMwGlobals( 'wgFlowExternalStore', false );

		// load actions object
		$this->actions = Container::get( 'flow_actions' );

		// block a user
		$blockedUser = $this->blockedUser();
		$this->block = new Block( array(
			'address' => $blockedUser->getName(),
			'user' => $blockedUser->getID()
		) );
		$this->block->insert();
		// ensure that block made it into the database
		wfGetDB( DB_MASTER )->commit( __METHOD__, 'flush' );
	}

	/**
	 * Provides User, permissions test action, and revision actions (with
	 * expected permission results for test action).
	 *
	 * Basically: a new post is created and the actions in $actions are
	 * performed. After that, we'll check if $action is allowed on all of those
	 * revisions, with the expected true/false value from $actions as result.
	 *
	 * @return array
	 */
	public function permissionsProvider() {
		return array(
			// irregardless of current status, if a user has no permissions for
			// a specific revision, he can't see it
			array( $this->confirmedUser(), 'view', array(
				// Key is the moderation action; value is the 'view' permission
				// for that corresponding revision after all moderation is done.
				// In this case, a post will be created with 3 revisions:
				// [1] create post, [2] suppress, [3] restore
				// After creating all revisions, all of these will be tested for
				// 'view' permissions against that specific revision. Here:
				// [1] should be visible (this + last rev not suppressed)
				// [2] should not (was suppressed)
				// [3] should be visible again (undid suppression)
				array( 'new-post' => true ),
				array( 'suppress-post' => false ),
				array( 'restore-post' => true ),
			) ),
			array( $this->oversightUser(), 'view', array(
				array( 'new-post' => true ),
				array( 'suppress-post' => true ),
				array( 'restore-post' => true ),
			) ),

			// last moderation status should always bubble down to previous revs
			array( $this->confirmedUser(), 'view', array(
				array( 'new-post' => false ),
				array( 'suppress-post' => false ),
				array( 'restore-post' => false ),
				array( 'suppress-post' => false ),
			) ),
			array( $this->oversightUser(), 'view', array(
				array( 'new-post' => true ),
				array( 'suppress-post' => true ),
				array( 'restore-post' => true ),
				array( 'suppress-post' => true ),
			) ),

			// bug 61715
			array( $this->confirmedUser(), 'history', array(
				array( 'new-post' => false ),
				array( 'suppress-post' => false ),
			) ),
			array( $this->confirmedUser(), 'history', array(
				array( 'new-post' => true ),
				array( 'suppress-post' => false ),
				array( 'restore-post' => false ),
			) ),
		);
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions( User $user, $permissionAction, $actions ) {
		$permissions = new RevisionActionPermissions( $this->actions, $user );

		// we'll have to process this in 2 steps: first do all of the actions,
		// so we have a full tree of moderated revisions
		$revision = null;
		$revisions = array();
		$debug = array();
		foreach ( $actions as $action ) {
			$expect = current( $action );
			$action = key( $action );
			$debug[] = $action . ':' . ( $expect ? 'true' : 'false' );
			$revisions[] = $revision = $this->generateRevision( $action, $revision );
		}

		// commit pending db transaction
		Container::get( 'db.factory' )->getDB( DB_MASTER )->commit( __METHOD__, 'flush' );

		$debug = implode( ' ', $debug );
		// secondly, iterate all revisions & see if expected permissions line up
		foreach ( $actions as $action ) {
			$expected = current( $action );
			$revision = array_shift( $revisions );
			$this->assertEquals(
				$expected,
				$permissions->isAllowed( $revision, $permissionAction ),
				'User ' . $user->getName() . ' should ' . ( $expected ? '' : 'not ' ) . 'be allowed action ' . $permissionAction . ' on revision ' . key( $action ) . ' : ' . $debug . ' : ' . json_encode( $revision::toStorageRow( $revision ) )
			);
		}
	}

	protected function blockedUser() {
		if ( !$this->blockedUser ) {
			$this->blockedUser = User::newFromName( 'UTFlowBlockee' );
			$this->blockedUser->addToDatabase();
			// note: the block will be added in setUp & deleted in tearDown;
			// otherwise this is just any regular user
		}

		return $this->blockedUser;
	}

	protected function anonUser() {
		if ( !$this->anonUser ) {
			$this->anonUser = new User;
		}

		return $this->anonUser;
	}

	protected function unconfirmedUser() {
		if ( !$this->unconfirmedUser ) {
			$this->unconfirmedUser = User::newFromName( 'UTFlowUnconfirmed' );
			$this->unconfirmedUser->addToDatabase();
			$this->unconfirmedUser->addGroup( 'user' );
		}

		return $this->unconfirmedUser;
	}

	protected function confirmedUser() {
		if ( !$this->confirmedUser ) {
			$this->confirmedUser = User::newFromName( 'UTFlowConfirmed' );
			$this->confirmedUser->addToDatabase();
			$this->confirmedUser->addGroup( 'autoconfirmed' );
		}

		return $this->confirmedUser;
	}

	protected function sysopUser() {
		if ( !$this->sysopUser ) {
			$this->sysopUser = User::newFromName( 'UTFlowSysop' );
			$this->sysopUser->addToDatabase();
			$this->sysopUser->addGroup( 'sysop' );
		}

		return $this->sysopUser;
	}

	protected function oversightUser() {
		if ( !$this->oversightUser ) {
			$this->oversightUser = User::newFromName( 'UTFlowOversight' );
			$this->oversightUser->addToDatabase();
			$this->oversightUser->addGroup( 'oversight' );
		}

		return $this->oversightUser;
	}

	/**
	 * @param string $action
	 * @param AbstractRevision|null $parent
	 * @param array $overrides
	 * @return PostRevision
	 */
	public function generateRevision( $action, AbstractRevision $parent = null, array $overrides = array() ) {
		$overrides['rev_change_type'] = $action;

		if ( $parent ) {
			$overrides['rev_parent_id'] = $parent->getRevisionId()->getBinary();
			$overrides['tree_rev_descendant_id'] = $parent->getPostId()->getBinary();
			$overrides['rev_type_id'] = $parent->getPostId()->getBinary();
		}

		switch ( $action ) {
			case 'restore-post':
				$overrides += array(
					'rev_mod_state' => $this->moderation[$action], // AbstractRevision::MODERATED_NONE
					'rev_mod_user_id' => null,
					'rev_mod_user_ip' => null,
					'rev_mod_timestamp' => null,
					'rev_mod_reason' => 'unit test',
				);
				break;

			case 'hide-post':
			case 'delete-post':
			case 'suppress-post':
				$overrides += array(
					'rev_mod_state' => $this->moderation[$action], // AbstractRevision::MODERATED_(HIDDEN|DELETED|SUPPRESSED)
					'rev_mod_user_id' => 1,
					'rev_mod_user_ip' => null,
					'rev_mod_timestamp' => wfTimestampNow(),
					'rev_mod_reason' => 'unit test',
				);
				break;

			default:
				// nothing special
				break;
		}

		$revision = $this->generateObject( $overrides );
		$this->store( $revision );

		return $revision;
	}
}
