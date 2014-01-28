<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\RecentChanges as RecentChangesHandler;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Model\UUID;
use User;

/**
 * @group Flow
 * @group Database
 */
class PostRevisionTestCase extends TestCase {
	/**
	 * @var PostRevision[]
	 */
	protected $revisions = array();

	/**
	 * @var Workflow
	 */
	protected $workflow;

	protected function setUp() {
		parent::setUp();
		Container::reset();
		$this->generateWorkflowForPost();
		$this->revision = $this->generateObject();
	}

	/**
	 * Reset the container and with it any state
	 */
	protected function tearDown() {
		parent::tearDown();

		foreach ( $this->revisions as $revision ) {
			try {
				$this->getStorage()->remove( $revision );
			} catch ( \MWException $e ) {
				// ignore - lifecyclehandlers may cause issues with tests, where
				// not all related stuff is loaded
			}
		}

		Container::get( 'storage.workflow' )->remove( $this->workflow );

		// Needed because not all cases do the reset in setUp yet
		Container::reset();
	}

	/**
	 * @return ObjectManager
	 */
	protected function getStorage() {
		return Container::get( 'storage.post' );
	}

	/**
	 * Returns an array, representing flow_revision & flow_tree_revision db
	 * columns.
	 *
	 * You can pass in arguments to override default data.
	 * With no arguments tossed in, default data (resembling a newly-created
	 * topic title) will be returned.
	 *
	 * @param array[optional] $row DB row data (only specify override columns)
	 * @return array
	 */
	protected function generateRow( array $row = array() ) {
		$this->generateWorkflowForPost();
		$uuidRevision = UUID::create();

		$user = User::newFromName( 'UTSysop' );
		list( $userId, $userIp ) = PostRevision::userFields( $user );

		return $row + array(
			// flow_revision
			'rev_id' => $uuidRevision->getBinary(),
			'rev_type' => 'post',
			'rev_type_id' => $this->workflow->getId()->getBinary(),
			'rev_user_id' => $userId,
			'rev_user_ip' => $userIp,
			'rev_parent_id' => null,
			'rev_flags' => 'html',
			'rev_content' => 'test content',
			'rev_change_type' => 'new-post',
			'rev_mod_state' => AbstractRevision::MODERATED_NONE,
			'rev_mod_user_id' => null,
			'rev_mod_user_ip' => null,
			'rev_mod_timestamp' => null,
			'rev_mod_reason' => null,
			'rev_last_edit_id' => null,
			'rev_edit_user_id' => null,
			'rev_edit_user_ip' => null,
			'rev_user_wiki' => wfWikiId(),
			'rev_mod_user_wiki' => null,
			'rev_edit_user_wiki' => null,

			// flow_tree_revision
			'tree_rev_descendant_id' => $this->workflow->getId()->getBinary(),
			'tree_rev_id' => $uuidRevision->getBinary(),
			'tree_orig_user_id' => $userId,
			'tree_orig_user_ip' => $userIp,
			'tree_parent_id' => null,
			'tree_orig_user_wiki' => wfWikiId(),
		);
	}

	/**
	 * Populate a fake workflow in the unittest database
	 */
	protected function generateWorkflowForPost() {
		if ( $this->workflow ) {
			return;
		}
		list( $userId, $userIp ) = PostRevision::userFields( User::newFromName( 'UTSysop' ) );

		$row = array(
			'workflow_id' => UUID::create()->getBinary(),
			'workflow_wiki' => wfWikiId(),
			// The test workflow has no real associated page, this is
			// just a random page number
			'workflow_page_id' => 1,
			'workflow_namespace' => NS_USER_TALK,
			'workflow_title_text' => 'Test',
			'workflow_user_id' => $userId,
			'workflow_user_ip' => $userIp,
			'workflow_lock_state' => 0,
			'workflow_definition_id' => UUID::create()->getBinary(),
			'workflow_last_update_timestamp' => wfTimestampNow(),
		);
		$this->workflow = Workflow::fromStorageRow( $row );
		Container::get( 'storage' )->put( $this->workflow );
	}

	/**
	 * Returns a PostRevision object.
	 *
	 * You can pass in arguments to override default data.
	 * With no arguments tossed in, a default revision (resembling a newly-
	 * created topic title) will be returned.
	 *
	 * @param array[optional] $row DB row data (only specify override columns)
	 * @param array[optional] $children Array of child PostRevision objects
	 * @param int[optional] $depth Depth of the PostRevision object
	 * @return PostRevision
	 */
	protected function generateObject( array $row = array(), $children = array(), $depth = 0 ) {
		$row = $this->generateRow( $row );

		$revision = PostRevision::fromStorageRow( $row );
		$revision->setChildren( $children );
		$revision->setDepth( $depth );

		return $revision;
	}

	/**
	 * Saves a PostRevision to storage.
	 * Be sure to add the required tables to $tablesUsed and add @group Database
	 * to the class' phpDoc.
	 *
	 * @param PostRevision $revision
	 */
	protected function store( PostRevision $revision ) {
		$this->getStorage()->put( $revision );

		// save for removal at end of tests
		$this->revisions[] = $revision;
	}

	protected function clearRecentChangesLifecycleHandlers() {
		// Recent changes logging is outside the scope of this test, and
		// causes interaction issues
		$c = Container::getContainer();
		foreach ( array( 'header', 'post' ) as $kind ) {
			$key = "storage.$kind.lifecycle-handlers";
			$c[$key] = array_filter(
				$c[$key],
				function( $handler ) {
					return !$handler instanceof RecentChangesHandler;
				}
			);
		}
	}
}
