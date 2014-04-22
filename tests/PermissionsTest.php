<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\PostRevision;
use Flow\Model\AbstractRevision;
use Flow\RevisionActionPermissions;
use User;
use Block;

/**
 * @group Database
 * @group Flow
 */
class PermissionsTest extends PostRevisionTestCase {
	/**
	 * @var array
	 */
	protected $tablesUsed = array( 'user', 'user_groups', 'ipblocks' );

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @var PostRevision
	 */
	protected
		$topic,
		$hiddenTopic,
		$deletedTopic,
		$suppressedTopic,
		$post,
		$hiddenPost,
		$deletedPost,
		$suppressedPost;

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

		// We don't want local config getting in the way of testing whether or
		// not our permissions implementation works well.
		// This will load default $wgGroupPermissions + Flow settings, so we can
		// test if permissions work well, regardless of any custom config.
		global $IP, $wgFlowGroupPermissions;
		$wgGroupPermissions = array();
		require "$IP/includes/DefaultSettings.php";
		$wgGroupPermissions = array_merge_recursive( $wgGroupPermissions, $wgFlowGroupPermissions );
		$this->setMwGlobals( 'wgGroupPermissions', $wgGroupPermissions );

		// load actions object
		$this->actions = Container::get( 'flow_actions' );

		// block a user
		$blockedUser = $this->blockedUser();
		$this->block = new Block( $blockedUser->getName(), $blockedUser->getID() );
		$this->block->insert();
	}

	protected function tearDown() {
		parent::tearDown();
		$this->block->delete();
	}

	/**
	 * Provides User, PostRevision (or null) & action to testPermissions, as
	 * well as the expected result: whether or not a certain user should be
	 * allowed to perform a certain action on a certain revision.
	 *
	 * I'm calling functions to fetch users & revisions. This is done because
	 * setUp is called only after dataProvider is executed, so it's impossible
	 * to create all these objects in setUp.
	 *
	 * "All data providers are executed before both the call to the
	 * setUpBeforeClass static method and the first call to the setUp method.
	 * Because of that you can't access any variables you create there from
	 * within a data provider. This is required in order for PHPUnit to be able
	 * to compute the total number of tests."
	 *
	 * @see http://phpunit.de/manual/3.7/en/writing-tests-for-phpunit.html
	 *
	 * @return array
	 */
	public function permissionsProvider() {
		return array(
			// blocked users can only read
			array( $this->blockedUser(), null, 'create-header', false ),
//			array( $this->blockedUser(), $this->header(), 'edit-header', false ),
			array( $this->blockedUser(), $this->post(), 'edit-title', false ),
			array( $this->blockedUser(), null, 'new-post', false ),
			array( $this->blockedUser(), $this->post(), 'edit-post', false ),
			array( $this->blockedUser(), $this->post(), 'hide-post', false ),
			array( $this->blockedUser(), $this->topic(), 'hide-topic', false ),
			array( $this->blockedUser(), $this->post(), 'delete-post', false ),
			array( $this->blockedUser(), $this->topic(), 'delete-topic', false ),
			array( $this->blockedUser(), $this->topic(), 'close-topic', false ),
			array( $this->blockedUser(), $this->post(), 'suppress-post', false ),
			array( $this->blockedUser(), $this->topic(), 'suppress-topic', false ),
			array( $this->blockedUser(), $this->post(), 'restore-post', false ),
			array( $this->blockedUser(), $this->topic(), 'restore-topic', false ),
			array( $this->blockedUser(), $this->post(), 'history', true ),
			array( $this->blockedUser(), $this->post(), 'view', true ),
			array( $this->blockedUser(), $this->post(), 'reply', false ),

			// anon users can submit content, but not moderate
			array( $this->anonUser(), null, 'create-header', true ),
//			array( $this->anonUser(), $this->header(), 'edit-header', true ),
			array( $this->anonUser(), $this->topic(), 'edit-title', true ),
			array( $this->anonUser(), null, 'new-post', true ),
			array( $this->anonUser(), $this->post(), 'edit-post', false ),
			array( $this->anonUser(), $this->post(), 'hide-post', false ),
			array( $this->anonUser(), $this->topic(), 'hide-topic', false ),
			array( $this->anonUser(), $this->topic(), 'close-topic', false ),
			array( $this->anonUser(), $this->post(), 'delete-post', false ),
			array( $this->anonUser(), $this->topic(), 'delete-topic', false ),
			array( $this->anonUser(), $this->post(), 'suppress-post', false ),
			array( $this->anonUser(), $this->topic(), 'suppress-topic', false ),
			array( $this->anonUser(), $this->post(), 'restore-post', false ),
			array( $this->anonUser(), $this->topic(), 'restore-topic', false ),
			array( $this->anonUser(), $this->post(), 'history', true ),
			array( $this->anonUser(), $this->post(), 'view', true ),
			array( $this->anonUser(), $this->post(), 'reply', true ),

			// unconfirmed users can also hide posts...
			array( $this->unconfirmedUser(), null, 'create-header', true ),
//			array( $this->unconfirmedUser(), $this->header(), 'edit-header', true ),
			array( $this->unconfirmedUser(), $this->topic(), 'edit-title', true ),
			array( $this->unconfirmedUser(), null, 'new-post', true ),
			array( $this->unconfirmedUser(), $this->post(), 'edit-post', true ), // can edit own post
			array( $this->unconfirmedUser(), $this->post(), 'hide-post', true ),
			array( $this->unconfirmedUser(), $this->topic(), 'hide-topic', true ),
			array( $this->unconfirmedUser(), $this->topic(), 'close-topic', true ),
			array( $this->unconfirmedUser(), $this->post(), 'delete-post', false ),
			array( $this->unconfirmedUser(), $this->topic(), 'delete-topic', false ),
			array( $this->unconfirmedUser(), $this->post(), 'suppress-post', false ),
			array( $this->unconfirmedUser(), $this->topic(), 'suppress-topic', false ),
			array( $this->unconfirmedUser(), $this->post(), 'restore-post', false ), // $this->post is not hidden
			array( $this->unconfirmedUser(), $this->topic(), 'restore-topic', false ), // $this->topic is not hidden
			array( $this->unconfirmedUser(), $this->post(), 'history', true ),
			array( $this->unconfirmedUser(), $this->post(), 'view', true ),
			array( $this->unconfirmedUser(), $this->post(), 'reply', true ),

			// ... as well as restore hidden posts
			array( $this->unconfirmedUser(), $this->hiddenPost(), 'restore-post', true ),
			array( $this->unconfirmedUser(), $this->hiddenTopic(), 'restore-topic', true ),

			// ... but not restore deleted/suppressed posts
			array( $this->unconfirmedUser(), $this->deletedPost(), 'restore-post', false ),
			array( $this->unconfirmedUser(), $this->deletedTopic(), 'restore-topic', false ),
			array( $this->unconfirmedUser(), $this->suppressedPost(), 'restore-post', false ),
			array( $this->unconfirmedUser(), $this->suppressedTopic(), 'restore-topic', false ),

			// confirmed users are the same as unconfirmed users, in terms of permissions
			array( $this->confirmedUser(), null, 'create-header', true ),
//			array( $this->confirmedUser(), $this->header(), 'edit-header', true ),
			array( $this->confirmedUser(), $this->topic(), 'edit-title', true ),
			array( $this->confirmedUser(), null, 'new-post', true ),
			array( $this->confirmedUser(), $this->post(), 'edit-post', false ),
			array( $this->confirmedUser(), $this->post(), 'hide-post', true ),
			array( $this->confirmedUser(), $this->topic(), 'hide-topic', true ),
			array( $this->confirmedUser(), $this->post(), 'delete-post', false ),
			array( $this->confirmedUser(), $this->topic(), 'delete-topic', false ),
			array( $this->confirmedUser(), $this->topic(), 'close-topic', true ),
			array( $this->confirmedUser(), $this->post(), 'suppress-post', false ),
			array( $this->confirmedUser(), $this->topic(), 'suppress-topic', false ),
			array( $this->confirmedUser(), $this->post(), 'restore-post', false ), // $this->post is not hidden
			array( $this->confirmedUser(), $this->topic(), 'restore-topic', false ), // $this->topic is not hidden
			array( $this->confirmedUser(), $this->post(), 'history', true ),
			array( $this->confirmedUser(), $this->post(), 'view', true ),
			array( $this->confirmedUser(), $this->post(), 'reply', true ),
			array( $this->confirmedUser(), $this->hiddenPost(), 'restore-post', true ),
			array( $this->confirmedUser(), $this->hiddenTopic(), 'restore-topic', true ),
			array( $this->confirmedUser(), $this->deletedPost(), 'restore-post', false ),
			array( $this->confirmedUser(), $this->deletedTopic(), 'restore-topic', false ),
			array( $this->confirmedUser(), $this->suppressedPost(), 'restore-post', false ),
			array( $this->confirmedUser(), $this->suppressedTopic(), 'restore-topic', false ),

			// sysops can do all (incl. editing posts) but suppressing
			array( $this->sysopUser(), null, 'create-header', true ),
//			array( $this->sysopUser(), $this->header(), 'edit-header', true ),
			array( $this->sysopUser(), $this->topic(), 'edit-title', true ),
			array( $this->sysopUser(), null, 'new-post', true ),
			array( $this->sysopUser(), $this->post(), 'edit-post', true ),
			array( $this->sysopUser(), $this->post(), 'hide-post', true ),
			array( $this->sysopUser(), $this->topic(), 'hide-topic', true ),
			array( $this->sysopUser(), $this->topic(), 'close-topic', true ),
			array( $this->sysopUser(), $this->post(), 'delete-post', true ),
			array( $this->sysopUser(), $this->topic(), 'delete-topic', true ),
			array( $this->sysopUser(), $this->post(), 'suppress-post', false ),
			array( $this->sysopUser(), $this->topic(), 'suppress-topic', false ),
			array( $this->sysopUser(), $this->post(), 'restore-post', false ), // $this->post is not hidden
			array( $this->sysopUser(), $this->topic(), 'restore-topic', false ), // $this->topic is not hidden
			array( $this->sysopUser(), $this->topic(), 'history', true ),
			array( $this->sysopUser(), $this->post(), 'view', true ),
			array( $this->sysopUser(), $this->post(), 'reply', true ),
			array( $this->sysopUser(), $this->hiddenPost(), 'restore-post', true ),
			array( $this->sysopUser(), $this->hiddenTopic(), 'restore-topic', true ),
			array( $this->sysopUser(), $this->deletedPost(), 'restore-post', true ),
			array( $this->sysopUser(), $this->deletedTopic(), 'restore-topic', true ),
			array( $this->sysopUser(), $this->suppressedPost(), 'restore-post', false ),
			array( $this->sysopUser(), $this->suppressedTopic(), 'restore-topic', false ),

			// oversighters can do everything + suppress (but not edit!)
			array( $this->oversightUser(), null, 'create-header', true ),
//			array( $this->oversightUser(), $this->header(), 'edit-header', true ),
			array( $this->oversightUser(), $this->topic(), 'edit-title', true ),
			array( $this->oversightUser(), null, 'new-post', true ),
			array( $this->oversightUser(), $this->post(), 'edit-post', false ),
			array( $this->oversightUser(), $this->post(), 'hide-post', true ),
			array( $this->oversightUser(), $this->topic(), 'hide-topic', true ),
			array( $this->oversightUser(), $this->topic(), 'close-topic', true ),
			array( $this->oversightUser(), $this->post(), 'delete-post', true ),
			array( $this->oversightUser(), $this->topic(), 'delete-topic', true ),
			array( $this->oversightUser(), $this->post(), 'suppress-post', true ),
			array( $this->oversightUser(), $this->topic(), 'suppress-topic', true ),
			array( $this->oversightUser(), $this->post(), 'restore-post', false ), // $this->post is not hidden
			array( $this->oversightUser(), $this->topic(), 'restore-topic', false ), // $this->topic is not hidden
			array( $this->oversightUser(), $this->post(), 'history', true ),
			array( $this->oversightUser(), $this->post(), 'view', true ),
			array( $this->oversightUser(), $this->post(), 'reply', true ),
			array( $this->oversightUser(), $this->hiddenPost(), 'restore-post', true ),
			array( $this->oversightUser(), $this->hiddenTopic(), 'restore-topic', true ),
			array( $this->oversightUser(), $this->deletedPost(), 'restore-post', true ),
			array( $this->oversightUser(), $this->deletedTopic(), 'restore-topic', true ),
			array( $this->oversightUser(), $this->suppressedPost(), 'restore-post', true ),
			array( $this->oversightUser(), $this->suppressedTopic(), 'restore-topic', true ),
		);
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions( User $user, PostRevision $revision = null, $action, $expected ) {
		$permissions = new RevisionActionPermissions( $this->actions, $user );
		$this->assertEquals( $expected, $permissions->isAllowed( $revision, $action ) );
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

	protected function topic() {
		if ( !$this->topic ) {
			$this->topic = $this->generateObject();
		}

		return $this->topic;
	}

	protected function hiddenTopic() {
		if ( !$this->hiddenTopic ) {
			$this->hiddenTopic = $this->generateObject( array(
				'rev_change_type' => 'hide-topic',
				'rev_mod_state' => AbstractRevision::MODERATED_HIDDEN
			) );
		}

		return $this->hiddenTopic;
	}

	protected function deletedTopic() {
		if ( !$this->deletedTopic ) {
			$this->deletedTopic = $this->generateObject( array(
				'rev_change_type' => 'delete-topic',
				'rev_mod_state' => AbstractRevision::MODERATED_DELETED
			) );
		}

		return $this->deletedTopic;
	}

	protected function suppressedTopic() {
		if ( !$this->suppressedTopic ) {
			$this->suppressedTopic = $this->generateObject( array(
				'rev_change_type' => 'suppress-topic',
				'rev_mod_state' => AbstractRevision::MODERATED_SUPPRESSED
			) );
		}

		return $this->suppressedTopic;
	}

	protected function post() {
		if ( !$this->post ) {
			$this->post = $this->generateObject( array(
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_parent_id' => $this->topic()->getPostId()->getBinary()
			), array(), 1 );
		}

		return $this->post;
	}

	protected function hiddenPost() {
		if ( !$this->hiddenPost ) {
			$this->hiddenPost = $this->generateObject( array(
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_parent_id' => $this->topic()->getPostId()->getBinary(),
				'rev_change_type' => 'hide-post',
				'rev_mod_state' => AbstractRevision::MODERATED_HIDDEN
			), array(), 1 );
		}

		return $this->hiddenPost;
	}

	protected function deletedPost() {
		if ( !$this->deletedPost ) {
			$this->deletedPost = $this->generateObject( array(
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_parent_id' => $this->topic()->getPostId()->getBinary(),
				'rev_change_type' => 'delete-post',
				'rev_mod_state' => AbstractRevision::MODERATED_DELETED
			), array(), 1 );
		}

		return $this->deletedPost;
	}

	protected function suppressedPost() {
		if ( !$this->suppressedPost ) {
			$this->suppressedPost = $this->generateObject( array(
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_parent_id' => $this->topic()->getPostId()->getBinary(),
				'rev_change_type' => 'suppress-post',
				'rev_mod_state' => AbstractRevision::MODERATED_SUPPRESSED
			), array(), 1 );
		}

		return $this->suppressedPost;
	}
}
