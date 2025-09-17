<?php

namespace Flow\Tests;

use Flow\Collection\PostCollection;
use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Hooks;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UserTuple;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\OccupationController;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\WikiMap\WikiMap;
use RuntimeException;
use SplQueue;
use Wikimedia\TestingAccessWrapper;

/**
 * @group Flow
 * @group Database
 */
class PostRevisionTestCase extends FlowTestCase {
	/**
	 * @var PostRevision[]
	 */
	protected $revisions = [];

	/**
	 * @var Workflow[]
	 */
	protected $workflows = [];

	protected function setUp(): void {
		parent::setUp();
		Hooks::resetFlowExtension();

		// Revisions must be blanked here otherwise phpunit run with --repeat will remember
		// ths revision list between multiple invocations of the test causing issues.
		$this->revisions = [];
	}

	/**
	 * Reset the container and with it any state
	 */
	protected function tearDown(): void {
		parent::tearDown();

		foreach ( $this->revisions as $revision ) {
			$workflow = $revision->getCollection()->getWorkflow();
			$this->getStorage()->multiRemove( [ $revision ], [ 'workflow' => $workflow ] );
		}

		foreach ( $this->workflows as $workflow ) {
			try {
				$this->getStorage()->multiRemove( [ $workflow ] );

				$found = $this->getStorage()->find( 'TopicListEntry', [ 'topic_id' => $workflow->getId() ] );
				if ( $found ) {
					$this->getStorage()->multiRemove( $found );
				}
			} catch ( FlowException $e ) {
				// nothing, was probably never stored...
			}
		}

		// Needed because not all cases do the reset in setUp yet
		Container::reset();
	}

	/**
	 * @return ManagerGroup
	 */
	protected function getStorage() {
		return Container::get( 'storage' );
	}

	/**
	 * Returns an array, representing flow_revision & flow_tree_revision db
	 * columns.
	 *
	 * You can pass in arguments to override default data.
	 * With no arguments tossed in, default data (resembling a newly-created
	 * topic title) will be returned.
	 *
	 * @param array $row DB row data (only specify override columns)
	 * @return array
	 */
	protected function generateRow( array $row = [] ) {
		$workflow = $this->generateWorkflow( [ 'workflow_type' => 'topic' ] );
		$uuidRevision = UUID::create();

		$user = $this->getTestSysop()->getUser();
		$tuple = UserTuple::newFromUser( $user );

		return $row + [
			// flow_revision
			'rev_id' => $uuidRevision->getBinary(),
			'rev_type' => 'post',
			'rev_type_id' => $workflow->getId()->getBinary(),
			'rev_user_wiki' => $tuple->wiki,
			'rev_user_id' => $tuple->id,
			'rev_user_ip' => $tuple->ip,
			'rev_parent_id' => null,
			'rev_flags' => 'html',
			'rev_content' => 'test content',
			'rev_change_type' => 'new-topic',
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
			'tree_rev_descendant_id' => $workflow->getId()->getBinary(),
			'tree_rev_id' => $uuidRevision->getBinary(),
			'tree_orig_user_wiki' => $tuple->wiki,
			'tree_orig_user_id' => $tuple->id,
			'tree_orig_user_ip' => $tuple->ip,
			'tree_parent_id' => null,
		];
	}

	/**
	 * Populate a fake workflow in the unittest database
	 *
	 * @param array $row
	 * @return Workflow
	 */
	protected function generateWorkflow( $row = [] ) {
		$row = $row + [
			'workflow_id' => UUID::create()->getBinary(),
			'workflow_type' => 'topic',
			'workflow_wiki' => WikiMap::getCurrentWikiId(),
			// The test workflow has no real associated page, this is
			// just a random page number
			'workflow_page_id' => 1,
			'workflow_namespace' => NS_USER_TALK,
			'workflow_title_text' => 'Test',
			'workflow_lock_state' => 0,
			'workflow_last_update_timestamp' => wfTimestampNow(),
		];
		$workflow = Workflow::fromStorageRow( $row );

		// store workflow:
		// * so we can retrieve it should we want to store it (see store())
		// * so we can remove it on tearDown
		$this->workflows[$workflow->getId()->getAlphadecimal()] = $workflow;

		return $workflow;
	}

	/**
	 * Returns a PostRevision object.
	 *
	 * You can pass in arguments to override default data.
	 * With no arguments tossed in, a default revision (resembling a newly-
	 * created topic title) will be returned.
	 *
	 * @note This must not be called from a data provider, since it accesses the database!
	 *
	 * @param array $row DB row data (only specify override columns)
	 * @param PostRevision[] $children
	 * @param int $depth Depth of the PostRevision object
	 * @return PostRevision
	 */
	protected function generateObject( array $row = [], $children = [], $depth = 0 ) {
		$row = $this->generateRow( $row );

		$revision = PostRevision::fromStorageRow( $row );
		$revision->setChildren( $children );
		$revision->setDepth( $depth );

		return $revision;
	}

	/**
	 * Saves a PostRevision to storage.
	 */
	protected function store( PostRevision $revision ) {
		if ( $revision->isTopicTitle() ) {
			$root = $revision;
		} else {
			/** @var PostCollection $parentCollection */
			$parentCollection = PostCollection::newFromId( $revision->getReplyToId() );
			$root = $parentCollection->getRoot()->getLastRevision();
		}
		$topicWorkflow = $this->workflows[$root->getCollectionId()->getAlphadecimal()];
		$boardWorkflow = Container::get( 'factory.loader.workflow' )
			->createWorkflowLoader( $topicWorkflow->getOwnerTitle() )
			->getWorkflow();

		$metadata = [
			'workflow' => $topicWorkflow,
			'board-workflow' => $boardWorkflow,
			// @todo: Topic.php also adds 'topic-title'
		];

		// check if this topic (+ workflow + board workflow + board page) have
		// already been inserted or do so now
		$found = $this->getStorage()->find( 'TopicListEntry', [ 'topic_id' => $topicWorkflow->getId() ] );
		if ( !$found ) {
			$title = $boardWorkflow->getOwnerTitle();
			$user = $this->getTestUser( [ 'autoconfirmed' ] )->getUser();

			/** @var OccupationController $occupationController */
			$occupationController = Container::get( 'occupation_controller' );
			// make sure user has rights to create board
			$permissionManager = $this->getServiceContainer()->getPermissionManager();
			$permissionManager->overrideUserRightsForTesting( $user,
				array_merge( $permissionManager->getUserPermissions( $user ), [ 'flow-create-board' ] )
			);
			$occupationController->safeAllowCreation( $title, $user );
			$wikiPage = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $title );
			$ensureStatus = $occupationController->ensureFlowRevision(
				$wikiPage,
				$boardWorkflow
			);
			if ( !$ensureStatus->isOK() ) {
				// This should help devs understand what's going on in the CI
				throw new RuntimeException( $ensureStatus->__toString() );
			}

			$topicListEntry = TopicListEntry::create( $boardWorkflow, $topicWorkflow );

			$this->getStorage()->put( $boardWorkflow, $metadata );
			$this->getStorage()->put( $topicWorkflow, $metadata );
			$this->getStorage()->put( $topicListEntry, $metadata );
		}

		$this->getStorage()->put( $revision, $metadata );

		/** @var SplQueue $deferredQueue */
		$deferredQueue = Container::get( 'deferred_queue' );
		while ( !$deferredQueue->isEmpty() ) {
			DeferredUpdates::addCallableUpdate( $deferredQueue->dequeue() );

			// doing updates 1 by 1 so an exception doesn't break others in
			// the queue
			DeferredUpdates::doUpdates();
		}

		// save for removal at end of tests
		$this->revisions[] = $revision;
	}

	protected function clearExtraLifecycleHandlers() {
		$container = Container::getContainer();

		// We want to remove some of the listeners from a few of the ObjectManager services;
		// entries in this array correspond to the key for the service in the container
		// (this will need to be written once the services are moved to ServiceWiring.php)
		$toUpdate = [ 'storage.workflow', 'storage.header', 'storage.post_summary', 'storage.post' ];
		foreach ( $toUpdate as $objectManagerName ) {
			$container->extend(
				$objectManagerName,
				static function ( $objectManager ) {
					$access = TestingAccessWrapper::newFromObject( $objectManager );

					// Prevent "Indirect modification of overloaded property
					// Wikimedia\TestingAccessWrapper::$lifecycleHandlers has no effect"
					// by getting the array and then setting it at the end
					$listeners = $access->lifecycleHandlers;

					// putting together the right metadata for a commit is beyond the
					// scope of these tests
					unset( $listeners['listeners.notification'] );

					// Recent changes logging is outside the scope of tests, and
					// causes interaction issues
					unset( $listeners['listener.recentchanges'] );

					// BoardHistory requires we also wire together TopicListEntry objects for
					// each revision, but that's also beyond our scope.
					unset( $listeners['storage.post_board_history.indexes.primary'] );

					// Update object
					$access->lifecycleHandlers = $listeners;

					// Return updated object
					return $objectManager;
				}
			);
		}
	}
}
