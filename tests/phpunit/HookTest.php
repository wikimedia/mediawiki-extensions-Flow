<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\Workflow;
use Flow\OccupationController;
use FlowHooks;
use RecentChange;
use Title;
use User;

/**
 * @group Flow
 */
class HookTest extends \MediaWikiTestCase {
	protected $tablesUsed = array(
		'flow_revision',
		'flow_topic_list',
		'flow_tree_node',
		'flow_tree_revision',
		'flow_workflow',
		'page',
		'revision',
		'text',
	);

	static public function onIRCLineURLProvider() {
		$user = User::newFromName( '127.0.0.1', false );

		// reset flow state, so everything ($container['permissions'])
		// uses this particular $user
		\FlowHooks::resetFlowExtension();
		Container::reset();
		$container = Container::getContainer();
		$container['user'] = $user;

		// data providers do not run in the same context as the actual test, as such we
		// can't create Title objects because they can have the wrong wikiID.  Instead we
		// pass closures into the test that create the objects within the correct context.
		$newHeader = function() use( $user ) {
			$title = Title::newFromText( 'Talk:Hook_test' );
			$workflow = Workflow::create( 'discussion', $title );
			$header = Header::create( $workflow, $user, 'header content', 'wikitext' );
			$metadata = array(
				'workflow' => $workflow,
				'revision' => $header,
			);

			/** @var OccupationController $occupationController */
			$occupationController = Container::get( 'occupation_controller' );
			// make sure user has rights to create board
			$user->mRights = array_merge( $user->getRights(), array( 'flow-create-board' ) );
			$occupationController->allowCreation( $title, $user );
			$occupationController->ensureFlowRevision( new \Article( $title ), $workflow );

			Container::get( 'storage' )->put( $workflow, $metadata );

			return $metadata;
		};
		$freshTopic = function() use( $user ) {
			$title = Title::newFromText( 'Talk:Hook_test' );
			$boardWorkflow = Workflow::create( 'discussion', $title );
			$topicWorkflow = Workflow::create( 'topic', $boardWorkflow->getArticleTitle() );
			$topicList = TopicListEntry::create( $boardWorkflow, $topicWorkflow );
			$topicTitle = PostRevision::create( $topicWorkflow, $user, 'some content', 'wikitext' );
			$metadata = array(
				'workflow' => $topicWorkflow,
				'board-workflow' => $boardWorkflow,
				'topic-title' => $topicTitle,
				'revision' => $topicTitle,
			);

			/** @var OccupationController $occupationController */
			$occupationController = Container::get( 'occupation_controller' );
			// make sure user has rights to create board
			$user->mRights = array_merge( $user->getRights(), array( 'flow-create-board' ) );
			$occupationController->allowCreation( $title, $user );
			$occupationController->ensureFlowRevision( new \Article( $title ), $boardWorkflow );

			$storage = Container::get( 'storage' );
			$storage->put( $boardWorkflow, $metadata );
			$storage->put( $topicWorkflow, $metadata );
			$storage->put( $topicList, $metadata );
			$storage->put( $topicTitle, $metadata );

			return $metadata;
		};
		$replyToTopic = function() use( $freshTopic, $user ) {
			$metadata = $freshTopic();
			$firstPost = $metadata['topic-title']->reply( $metadata['workflow'], $user, 'ffuts dna ylper', 'wikitext' );
			$metadata = array(
				'first-post' => $firstPost,
				'revision' => $firstPost,
			) + $metadata;

			Container::get( 'storage.post' )->put( $firstPost, $metadata );

			return $metadata;
		};

		return array(
			array(
				// test message
				'Freshly created topic',
				// flow-workflow-change attribute within rc_params
				$freshTopic,
				// expected query parameters
				array(
					'action' => 'history',
				),
			),

			array(
				'Reply to topic',
				$replyToTopic,
				array(
					'action' => 'history',
				),
			),

			array(
				'Edit topic title',
				function() use( $freshTopic, $user ) {
					$metadata = $freshTopic();
					$title = $metadata['workflow']->getArticleTitle();

					return array(
						'revision' => $metadata['revision']->newNextRevision( $user, 'gnihtemos gnihtemos', 'wikitext', 'edit-title', $title ),
					) + $metadata;
				},
				array(
					'action' => 'compare-post-revisions',
				),
			),

			array(
				'Edit post',
				function() use( $replyToTopic, $user ) {
					$metadata = $replyToTopic();
					$title = $metadata['workflow']->getArticleTitle();
					return array(
						'revision' => $metadata['revision']->newNextRevision( $user, 'IT\'S CAPS LOCKS DAY!', 'wikitext', 'edit-post', $title ),
					) + $metadata;
				},
				array(
					'action' => 'compare-post-revisions',
				),
			),

			array(
				'Edit board header',
				function() use ( $newHeader, $user ) {
					$metadata = $newHeader();
					$title = $metadata['workflow']->getArticleTitle();
					return array(
						'revision' => $metadata['revision']->newNextRevision( $user, 'STILL CAPS LOCKS DAY!', 'wikitext', 'edit-header', $title ),
					) + $metadata;
				},
				array(
					'action' => 'compare-header-revisions',
				),
			),

			array(
				'Moderate a post',
				function() use ( $replyToTopic, $user ) {
					$metadata = $replyToTopic();
					return array(
						'revision' => $metadata['revision']->moderate(
							$user,
							$metadata['revision']::MODERATED_DELETED,
							'delete-post',
							'something about cruise control'
						),
					) + $metadata;
				},
				array(
					'action' => 'history',
				),
			),

			array(
				'Moderate a topic',
				function() use ( $freshTopic, $user ) {
					$metadata = $freshTopic();
					return array(
						'revision' => $metadata['revision']->moderate(
							$user,
							$metadata['revision']::MODERATED_HIDDEN,
							'hide-topic',
							'adorable kittens'
						),
					) + $metadata;
				},
				array(
					'action' => 'history',
				),
			),
		);
	}

	/**
	 * @dataProvider onIRCLineUrlProvider
	 */
	public function testOnIRCLineUrl( $message, $metadataGen, $expectedQuery ) {
		$rc = new RecentChange;
		$rc->mAttribs = array(
			'rc_namespace' => 0,
			'rc_title' => 'Main Page',
			'rc_source' => RecentChangesListener::SRC_FLOW,
		);
		$metadata = $metadataGen();
		Container::get( 'formatter.irclineurl' )->associate( $rc, $metadata );

		$url = 'unset';
		$query = 'unset';
		$this->assertTrue( FlowHooks::onIRCLineURL( $url, $query, $rc ) );
		$expectedQuery['title'] = $metadata['workflow']->getArticleTitle()->getPrefixedDBkey();

		$parts = parse_url( $url );
		$this->assertArrayHasKey( 'query', $parts, $url );
		parse_str( $parts['query'], $queryParts );
		foreach ( $expectedQuery as $key => $value ) {
			$this->assertEquals( $value, $queryParts[$key], "Query part $key" );
		}
		$this->assertEquals( '', $query, $message );
	}
}
