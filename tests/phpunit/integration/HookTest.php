<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Formatter\CheckUserQuery;
use Flow\Hooks;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\Workflow;
use Flow\OccupationController;
use MediaWiki\CheckUser\Hook\HookRunner;
use MediaWiki\MediaWikiServices;
use MediaWiki\RecentChanges\RecentChange;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentityValue;
use MediaWikiIntegrationTestCase;

/**
 * @covers \Flow\Hooks
 *
 * @group Flow
 * @group Database
 */
class HookTest extends MediaWikiIntegrationTestCase {
	public static function onIRCLineURLProvider() {
		// data providers do not run in the same context as the actual test, as such we
		// can't create Title objects because they can have the wrong wikiID.  Instead we
		// pass closures into the test that create the objects within the correct context.
		$newHeader = static function ( User $user ) {
			$title = Title::newFromText( 'Talk:Hook_test' );
			$workflow = Container::get( 'factory.loader.workflow' )
				->createWorkflowLoader( $title )
				->getWorkflow();
			$header = Header::create( $workflow, $user, 'header content', 'wikitext' );
			$metadata = [
				'workflow' => $workflow,
				'revision' => $header,
			];

			/** @var OccupationController $occupationController */
			$occupationController = Container::get( 'occupation_controller' );
			// make sure user has rights to create board
			$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
			$permissionManager->overrideUserRightsForTesting( $user,
				array_merge( $permissionManager->getUserPermissions( $user ), [ 'flow-create-board' ] )
			);
			$occupationController->safeAllowCreation( $title, $user );
			$occupationController->ensureFlowRevision(
				MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title ),
				$workflow
			);

			Container::get( 'storage' )->put( $workflow, $metadata );

			return $metadata;
		};
		$freshTopic = static function ( User $user ) {
			$title = Title::newFromText( 'Talk:Hook_test' );
			$boardWorkflow = Container::get( 'factory.loader.workflow' )
				->createWorkflowLoader( $title )
				->getWorkflow();
			$topicWorkflow = Workflow::create( 'topic', $boardWorkflow->getArticleTitle() );
			$topicList = TopicListEntry::create( $boardWorkflow, $topicWorkflow );
			$topicTitle = PostRevision::createTopicPost( $topicWorkflow, $user, 'some content' );
			$metadata = [
				'workflow' => $topicWorkflow,
				'board-workflow' => $boardWorkflow,
				'topic-title' => $topicTitle,
				'revision' => $topicTitle,
			];

			/** @var OccupationController $occupationController */
			$occupationController = Container::get( 'occupation_controller' );
			// make sure user has rights to create board
			$permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
			$permissionManager->overrideUserRightsForTesting( $user,
				array_merge( $permissionManager->getUserPermissions( $user ), [ 'flow-create-board' ] )
			);
			$occupationController->safeAllowCreation( $title, $user );
			$occupationController->ensureFlowRevision(
				MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title ),
				$boardWorkflow
			);

			$storage = Container::get( 'storage' );
			$storage->put( $boardWorkflow, $metadata );
			$storage->put( $topicWorkflow, $metadata );
			$storage->put( $topicList, $metadata );
			$storage->put( $topicTitle, $metadata );

			return $metadata;
		};
		$replyToTopic = static function ( User $user ) use( $freshTopic ) {
			$metadata = $freshTopic( $user );
			$firstPost = $metadata['topic-title']->reply( $metadata['workflow'], $user, 'ffuts dna ylper', 'wikitext' );
			$metadata = [
				'first-post' => $firstPost,
				'revision' => $firstPost,
			] + $metadata;

			Container::get( 'storage.post' )->put( $firstPost, $metadata );

			return $metadata;
		};

		return [
			[
				// test message
				'Freshly created topic',
				// flow-workflow-change attribute within rc_params
				$freshTopic,
				// expected query parameters
				[
					'action' => 'history',
				],
			],

			[
				'Reply to topic',
				$replyToTopic,
				[
					'action' => 'history',
				],
			],

			[
				'Edit topic title',
				static function ( $user ) use( $freshTopic ) {
					$metadata = $freshTopic( $user );
					$title = $metadata['workflow']->getArticleTitle();

					return [
						'revision' => $metadata['revision']->newNextRevision( $user, 'gnihtemos gnihtemos',
							'topic-title-wikitext', 'edit-title', $title ),
					] + $metadata;
				},
				[
					'action' => 'compare-post-revisions',
				],
			],

			[
				'Edit post',
				static function ( $user ) use( $replyToTopic ) {
					$metadata = $replyToTopic( $user );
					$title = $metadata['workflow']->getArticleTitle();
					return [
						'revision' => $metadata['revision']->newNextRevision( $user, 'IT\'S CAPS LOCKS DAY!',
							'wikitext', 'edit-post', $title ),
					] + $metadata;
				},
				[
					'action' => 'compare-post-revisions',
				],
			],

			[
				'Edit board header',
				static function ( $user ) use ( $newHeader ) {
					$metadata = $newHeader( $user );
					$title = $metadata['workflow']->getArticleTitle();
					return [
						'revision' => $metadata['revision']->newNextRevision( $user, 'STILL CAPS LOCKS DAY!',
							'wikitext', 'edit-header', $title ),
					] + $metadata;
				},
				[
					'action' => 'compare-header-revisions',
				],
			],

			[
				'Moderate a post',
				static function ( $user ) use ( $replyToTopic ) {
					$metadata = $replyToTopic( $user );
					return [
						'revision' => $metadata['revision']->moderate(
							$user,
							$metadata['revision']::MODERATED_DELETED,
							'delete-post',
							'something about cruise control'
						),
					] + $metadata;
				},
				[
					'action' => 'history',
				],
			],

			[
				'Moderate a topic',
				static function ( $user ) use ( $freshTopic ) {
					$metadata = $freshTopic( $user );
					return [
						'revision' => $metadata['revision']->moderate(
							$user,
							$metadata['revision']::MODERATED_HIDDEN,
							'hide-topic',
							'adorable kittens'
						),
					] + $metadata;
				},
				[
					'action' => 'history',
				],
			],
		];
	}

	/**
	 * @dataProvider onIRCLineUrlProvider
	 */
	public function testOnIRCLineUrl( $message, $metadataGen, $expectedQuery ) {
		$user = $this->getTestUser()->getUser();

		// reset flow state, so everything ($container['permissions'])
		// uses this particular $user
		Hooks::resetFlowExtension();
		Container::reset();
		$container = Container::getContainer();
		$container['user'] = $user;

		$rc = new RecentChange;
		$rc->setAttribs( [
			'rc_namespace' => 0,
			'rc_title' => 'Main Page',
			'rc_source' => RecentChangesListener::SRC_FLOW,
		] );
		$metadata = $metadataGen( $user );
		Container::get( 'formatter.irclineurl' )->associate( $rc, $metadata );

		$url = 'unset';
		$query = 'unset';

		( new Hooks )->onIRCLineURL( $url, $query, $rc );
		$expectedQuery['title'] = $metadata['workflow']->getArticleTitle()->getPrefixedDBkey();

		$parts = parse_url( $url );
		$this->assertArrayHasKey( 'query', $parts, $url );
		parse_str( $parts['query'], $queryParts );
		foreach ( $expectedQuery as $key => $value ) {
			$this->assertEquals( $value, $queryParts[$key], "Query part $key" );
		}
		$this->assertSame( '', $query, $message );
	}

	public function testOnCheckUserInsertChangesRow() {
		$this->markTestSkippedIfExtensionNotLoaded( 'CheckUser' );
		// Tests that the hook correctly modifies the cu_changes row when a Flow change is made
		// and that the hook is run when the hook is simulated to be run.
		$rc = new RecentChange;
		$rc->setAttribs( [
			'rc_source' => RecentChangesListener::SRC_FLOW,
			'rc_params' => serialize( [ 'flow-workflow-change' => [
				'action' => 'action',
				'workflow' => 'workflow',
				'revision' => 'revision',
			] ] ),
		] );
		// $row, $ip, and $xff are passed by reference.
		$row = [];
		$ip = 'unused';
		$xff = 'unused';
		( new HookRunner( $this->getServiceContainer()->getHookContainer() ) )
			->onCheckUserInsertChangesRow( $ip, $xff, $row, UserIdentityValue::newAnonymous( '127.0.0.1' ), $rc );
		// Verify that the $row now has the expected cuc_comment value.
		$this->assertArrayEquals(
			[ 'cuc_comment' => CheckUserQuery::VERSION_PREFIX . ',action,workflow,revision' ], $row, true, true,
			'The $row argument passed by reference was not as expected after the hook was run.'
		);
	}
}
