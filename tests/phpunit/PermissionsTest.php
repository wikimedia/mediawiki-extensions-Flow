<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\RevisionActionPermissions;
use User;

/**
 * @group Database
 * @group Flow
 */
class PermissionsTest extends PostRevisionTestCase {
	/**
	 * @var array
	 */
	protected $tablesUsed = [ 'user', 'user_groups' ];

	/**
	 * @var FlowActions
	 */
	protected $actions;

	/**
	 * @var PostRevision
	 */
	protected $topic;

	/**
	 * @var PostRevision
	 */
	protected $hiddenTopic;

	/**
	 * @var PostRevision
	 */
	protected $deletedTopic;

	/**
	 * @var PostRevision
	 */
	protected $suppressedTopic;

	/**
	 * @var PostRevision
	 */
	protected $post;

	/**
	 * @var PostRevision
	 */
	protected $hiddenPost;

	/**
	 * @var PostRevision
	 */
	protected $deletedPost;

	/**
	 * @var PostRevision
	 */
	protected $suppressedPost;

	/**
	 * @var User
	 */
	protected $anonUser;

	/**
	 * @var User
	 */
	protected $unconfirmedUser;

	/**
	 * @var User
	 */
	protected $confirmedUser;

	/**
	 * @var User
	 */
	protected $sysopUser;

	/**
	 * @var User
	 */
	protected $oversightUser;

	protected function setUp() {
		parent::setUp();

		// We don't want local config getting in the way of testing whether or
		// not our permissions implementation works well.
		$this->resetPermissions();

		// load actions object
		$this->actions = Container::get( 'flow_actions' );
	}

	protected function tearDown() {
		parent::tearDown();
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
		return [
			// anon users can submit content, but not moderate
			[ $this->anonUser(), null, 'create-header', true ],
			// array( $this->anonUser(), $this->header(), 'edit-header', true ),
			[ $this->anonUser(), $this->topic(), 'edit-title', true ],
			[ $this->anonUser(), null, 'new-post', true ],
			[ $this->anonUser(), $this->post(), 'edit-post', false ],
			[ $this->anonUser(), $this->post(), 'hide-post', true ],
			[ $this->anonUser(), $this->topic(), 'hide-topic', true ],
			[ $this->anonUser(), $this->topic(), 'lock-topic', false ],
			[ $this->anonUser(), $this->post(), 'delete-post', false ],
			[ $this->anonUser(), $this->topic(), 'delete-topic', false ],
			[ $this->anonUser(), $this->post(), 'suppress-post', false ],
			[ $this->anonUser(), $this->topic(), 'suppress-topic', false ],
			[ $this->anonUser(), $this->post(), 'restore-post', false ],
			[ $this->anonUser(), $this->topic(), 'restore-topic', false ],
			[ $this->anonUser(), $this->post(), 'history', true ],
			[ $this->anonUser(), $this->post(), 'view', true ],
			[ $this->anonUser(), $this->post(), 'reply', true ],

			// unconfirmed users can also hide posts...
			[ $this->unconfirmedUser(), null, 'create-header', true ],
			// array( $this->unconfirmedUser(), $this->header(), 'edit-header', true ),
			[ $this->unconfirmedUser(), $this->topic(), 'edit-title', true ],
			[ $this->unconfirmedUser(), null, 'new-post', true ],
			[ $this->unconfirmedUser(), $this->post(), 'edit-post', true ], // can edit own post
			[ $this->unconfirmedUser(), $this->post(), 'hide-post', true ],
			[ $this->unconfirmedUser(), $this->topic(), 'hide-topic', true ],
			[ $this->unconfirmedUser(), $this->topic(), 'lock-topic', true ],
			[ $this->unconfirmedUser(), $this->post(), 'delete-post', false ],
			[ $this->unconfirmedUser(), $this->topic(), 'delete-topic', false ],
			[ $this->unconfirmedUser(), $this->post(), 'suppress-post', false ],
			[ $this->unconfirmedUser(), $this->topic(), 'suppress-topic', false ],
			[ $this->unconfirmedUser(), $this->post(), 'restore-post', false ], // $this->post is not hidden
			[ $this->unconfirmedUser(), $this->topic(), 'restore-topic', false ], // $this->topic is not hidden
			[ $this->unconfirmedUser(), $this->post(), 'history', true ],
			[ $this->unconfirmedUser(), $this->post(), 'view', true ],
			[ $this->unconfirmedUser(), $this->post(), 'reply', true ],

			// ... as well as restore hidden posts
			[ $this->unconfirmedUser(), $this->hiddenPost(), 'restore-post', true ],
			[ $this->unconfirmedUser(), $this->hiddenTopic(), 'restore-topic', true ],

			// ... but not restore deleted/suppressed posts
			[ $this->unconfirmedUser(), $this->deletedPost(), 'restore-post', false ],
			[ $this->unconfirmedUser(), $this->deletedTopic(), 'restore-topic', false ],
			[ $this->unconfirmedUser(), $this->suppressedPost(), 'restore-post', false ],
			[ $this->unconfirmedUser(), $this->suppressedTopic(), 'restore-topic', false ],

			// confirmed users are the same as unconfirmed users, in terms of permissions
			[ $this->confirmedUser(), null, 'create-header', true ],
			// array( $this->confirmedUser(), $this->header(), 'edit-header', true ),
			[ $this->confirmedUser(), $this->topic(), 'edit-title', true ],
			[ $this->confirmedUser(), null, 'new-post', true ],
			[ $this->confirmedUser(), $this->post(), 'edit-post', false ],
			[ $this->confirmedUser(), $this->post(), 'hide-post', true ],
			[ $this->confirmedUser(), $this->topic(), 'hide-topic', true ],
			[ $this->confirmedUser(), $this->post(), 'delete-post', false ],
			[ $this->confirmedUser(), $this->topic(), 'delete-topic', false ],
			[ $this->confirmedUser(), $this->topic(), 'lock-topic', true ],
			[ $this->confirmedUser(), $this->post(), 'suppress-post', false ],
			[ $this->confirmedUser(), $this->topic(), 'suppress-topic', false ],
			[ $this->confirmedUser(), $this->post(), 'restore-post', false ], // $this->post is not hidden
			[ $this->confirmedUser(), $this->topic(), 'restore-topic', false ], // $this->topic is not hidden
			[ $this->confirmedUser(), $this->post(), 'history', true ],
			[ $this->confirmedUser(), $this->post(), 'view', true ],
			[ $this->confirmedUser(), $this->post(), 'reply', true ],
			[ $this->confirmedUser(), $this->hiddenPost(), 'restore-post', true ],
			[ $this->confirmedUser(), $this->hiddenTopic(), 'restore-topic', true ],
			[ $this->confirmedUser(), $this->deletedPost(), 'restore-post', false ],
			[ $this->confirmedUser(), $this->deletedTopic(), 'restore-topic', false ],
			[ $this->confirmedUser(), $this->suppressedPost(), 'restore-post', false ],
			[ $this->confirmedUser(), $this->suppressedTopic(), 'restore-topic', false ],

			// sysops can do all (incl. editing posts) but suppressing
			[ $this->sysopUser(), null, 'create-header', true ],
			// array( $this->sysopUser(), $this->header(), 'edit-header', true ),
			[ $this->sysopUser(), $this->topic(), 'edit-title', true ],
			[ $this->sysopUser(), null, 'new-post', true ],
			[ $this->sysopUser(), $this->post(), 'edit-post', true ],
			[ $this->sysopUser(), $this->post(), 'hide-post', true ],
			[ $this->sysopUser(), $this->topic(), 'hide-topic', true ],
			[ $this->sysopUser(), $this->topic(), 'lock-topic', true ],
			[ $this->sysopUser(), $this->post(), 'delete-post', true ],
			[ $this->sysopUser(), $this->topic(), 'delete-topic', true ],
			[ $this->sysopUser(), $this->post(), 'suppress-post', false ],
			[ $this->sysopUser(), $this->topic(), 'suppress-topic', false ],
			[ $this->sysopUser(), $this->post(), 'restore-post', false ], // $this->post is not hidden
			[ $this->sysopUser(), $this->topic(), 'restore-topic', false ], // $this->topic is not hidden
			[ $this->sysopUser(), $this->topic(), 'history', true ],
			[ $this->sysopUser(), $this->post(), 'view', true ],
			[ $this->sysopUser(), $this->post(), 'reply', true ],
			[ $this->sysopUser(), $this->hiddenPost(), 'restore-post', true ],
			[ $this->sysopUser(), $this->hiddenTopic(), 'restore-topic', true ],
			[ $this->sysopUser(), $this->deletedPost(), 'restore-post', true ],
			[ $this->sysopUser(), $this->deletedTopic(), 'restore-topic', true ],
			[ $this->sysopUser(), $this->suppressedPost(), 'restore-post', false ],
			[ $this->sysopUser(), $this->suppressedTopic(), 'restore-topic', false ],

			// oversighters can do everything + suppress (but not edit!)
			[ $this->oversightUser(), null, 'create-header', true ],
			// array( $this->oversightUser(), $this->header(), 'edit-header', true ),
			[ $this->oversightUser(), $this->topic(), 'edit-title', true ],
			[ $this->oversightUser(), null, 'new-post', true ],
			[ $this->oversightUser(), $this->post(), 'edit-post', false ],
			[ $this->oversightUser(), $this->post(), 'hide-post', true ],
			[ $this->oversightUser(), $this->topic(), 'hide-topic', true ],
			[ $this->oversightUser(), $this->topic(), 'lock-topic', true ],
			[ $this->oversightUser(), $this->post(), 'delete-post', true ],
			[ $this->oversightUser(), $this->topic(), 'delete-topic', true ],
			[ $this->oversightUser(), $this->post(), 'suppress-post', true ],
			[ $this->oversightUser(), $this->topic(), 'suppress-topic', true ],
			[ $this->oversightUser(), $this->post(), 'restore-post', false ], // $this->post is not hidden
			[ $this->oversightUser(), $this->topic(), 'restore-topic', false ], // $this->topic is not hidden
			[ $this->oversightUser(), $this->post(), 'history', true ],
			[ $this->oversightUser(), $this->post(), 'view', true ],
			[ $this->oversightUser(), $this->post(), 'reply', true ],
			[ $this->oversightUser(), $this->hiddenPost(), 'restore-post', true ],
			[ $this->oversightUser(), $this->hiddenTopic(), 'restore-topic', true ],
			[ $this->oversightUser(), $this->deletedPost(), 'restore-post', true ],
			[ $this->oversightUser(), $this->deletedTopic(), 'restore-topic', true ],
			[ $this->oversightUser(), $this->suppressedPost(), 'restore-post', true ],
			[ $this->oversightUser(), $this->suppressedTopic(), 'restore-topic', true ],
		];
	}

	/**
	 * @dataProvider permissionsProvider
	 */
	public function testPermissions( User $user, PostRevision $revision = null, $action, $expected ) {
		$permissions = new RevisionActionPermissions( $this->actions, $user );
		$this->assertEquals( $expected, $permissions->isRevisionAllowed( $revision, $action ) );
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
			$this->hiddenTopic = $this->generateObject( [
				'rev_change_type' => 'hide-topic',
				'rev_mod_state' => AbstractRevision::MODERATED_HIDDEN
			] );
		}

		return $this->hiddenTopic;
	}

	protected function deletedTopic() {
		if ( !$this->deletedTopic ) {
			$this->deletedTopic = $this->generateObject( [
				'rev_change_type' => 'delete-topic',
				'rev_mod_state' => AbstractRevision::MODERATED_DELETED
			] );
		}

		return $this->deletedTopic;
	}

	protected function suppressedTopic() {
		if ( !$this->suppressedTopic ) {
			$this->suppressedTopic = $this->generateObject( [
				'rev_change_type' => 'suppress-topic',
				'rev_mod_state' => AbstractRevision::MODERATED_SUPPRESSED
			] );
		}

		return $this->suppressedTopic;
	}

	protected function post() {
		if ( !$this->post ) {
			$this->post = $this->generateObject( [
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_orig_user_ip' => '',
				'tree_parent_id' => $this->topic()->getPostId()->getBinary()
			], [], 1 );
			$this->post->setRootPost( $this->generateObject( [
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_orig_user_ip' => '',
				'tree_parent_id' => $this->topic()->getPostId()->getBinary()
			], [], 1 ) );
		}

		return $this->post;
	}

	protected function hiddenPost() {
		if ( !$this->hiddenPost ) {
			$this->hiddenPost = $this->generateObject( [
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_orig_user_ip' => '',
				'tree_parent_id' => $this->topic()->getPostId()->getBinary(),
				'rev_change_type' => 'hide-post',
				'rev_mod_state' => AbstractRevision::MODERATED_HIDDEN
			], [], 1 );
		}

		return $this->hiddenPost;
	}

	protected function deletedPost() {
		if ( !$this->deletedPost ) {
			$this->deletedPost = $this->generateObject( [
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_orig_user_ip' => '',
				'tree_parent_id' => $this->topic()->getPostId()->getBinary(),
				'rev_change_type' => 'delete-post',
				'rev_mod_state' => AbstractRevision::MODERATED_DELETED
			], [], 1 );
		}

		return $this->deletedPost;
	}

	protected function suppressedPost() {
		if ( !$this->suppressedPost ) {
			$this->suppressedPost = $this->generateObject( [
				'tree_orig_user_id' => $this->unconfirmedUser()->getId(),
				'tree_orig_user_ip' => '',
				'tree_parent_id' => $this->topic()->getPostId()->getBinary(),
				'rev_change_type' => 'suppress-post',
				'rev_mod_state' => AbstractRevision::MODERATED_SUPPRESSED
			], [], 1 );
		}

		return $this->suppressedPost;
	}
}
