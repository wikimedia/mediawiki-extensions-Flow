<?php

namespace Flow\Tests;

use DeferredUpdates;
use Flow\Container;
use Flow\Data\Index\BoardHistoryIndex;
use Flow\Data\Listener\NotificationListener;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Data\ObjectManager;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Model\UserTuple;
use Flow\Model\UUID;
use SplQueue;
use User;

/**
 * @group Flow
 * @group Database
 */
class PostRevisionTestCase extends FlowTestCase {
	/**
	 * @var PostRevision
	 */
	protected $revision;

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
		$this->generateWorkflowForPost();
		$this->revision = $this->generateObject();
		// Revisions must be blanked here otherwise phpunit run with --repeat will remember
		// ths revision list between multiple invocations of the test causing issues.
		$this->revisions = array();
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
		$tuple = UserTuple::newFromUser( $user );

		return $row + array(
			// flow_revision
			'rev_id' => $uuidRevision->getBinary(),
			'rev_type' => 'post',
			'rev_type_id' => $this->workflow->getId()->getBinary(),
			'rev_user_wiki' => $tuple->wiki,
			'rev_user_id' => $tuple->id,
			'rev_user_ip' => $tuple->ip,
			'rev_parent_id' => null,
			'rev_flags' => 'html',
			'rev_content' => 'test content',
			'rev_change_type' => 'new-post',
			'rev_mod_state' => AbstractRevision::MODERATED_NONE,
			'rev_mod_user_wiki' => null,
			'rev_mod_user_id' => null,
			'rev_mod_user_ip' => null,
			'rev_mod_timestamp' => null,
			'rev_mod_reason' => null,
			'rev_last_edit_id' => null,
			'rev_edit_user_wiki' => null,
			'rev_edit_user_id' => null,
			'rev_edit_user_ip' => null,
			'rev_content_length' => 0,
			'rev_previous_content_length' => 0,

			// flow_tree_revision
			'tree_rev_descendant_id' => $this->workflow->getId()->getBinary(),
			'tree_rev_id' => $uuidRevision->getBinary(),
			'tree_orig_user_wiki' => $tuple->wiki,
			'tree_orig_user_id' => $tuple->id,
			'tree_orig_user_ip' => $tuple->ip,
			'tree_parent_id' => null,
		);
	}

	/**
	 * Populate a fake workflow in the unittest database
	 *
	 * @return Workflow
	 */
	protected function generateWorkflowForPost() {
		if ( $this->workflow ) {
			return $this->workflow;
		}

		$row = array(
			'workflow_id' => UUID::create()->getBinary(),
			'workflow_type' => 'topic',
			'workflow_wiki' => wfWikiId(),
			// The test workflow has no real associated page, this is
			// just a random page number
			'workflow_page_id' => 1,
			'workflow_namespace' => NS_USER_TALK,
			'workflow_title_text' => 'Test',
			'workflow_lock_state' => 0,
			'workflow_last_update_timestamp' => wfTimestampNow(),
		);
		$this->workflow = Workflow::fromStorageRow( $row );

		return $this->workflow;
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
		$this->getStorage()->put(
			$revision,
			array(
				'workflow' => $this->generateWorkflowForPost(),
				// @todo: Topic.php also adds 'topic-title'
			)
		);

		/** @var SplQueue $deferredQueue */
		$deferredQueue = Container::get( 'deferred_queue' );
		while( !$deferredQueue->isEmpty() ) {
			try {
				DeferredUpdates::addCallableUpdate( $deferredQueue->dequeue() );

				// doing updates 1 by 1 so an exception doesn't break others in
				// the queue
				DeferredUpdates::doUpdates();
			} catch ( \MWException $e ) {
				// ignoring exceptions for now, not all are phpunit-proof yet
			}
		}

		// save for removal at end of tests
		$this->revisions[] = $revision;
	}
}
