<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use FlowHooks;
use RecentChange;
use Title;
use User;

/**
 * @group Flow
 */
class HookTest extends \MediaWikiTestCase {
	static public function onIRCLineURLProvider() {
		$user = User::newFromName( '127.0.0.1', false );
		$title = Title::newMainPage();

		// data providers do not run in the same context as the actual test, as such we
		// can't create Title objects because they can have the wrong wikiID.  Instead we
		// pass closures into the test that create the objects within the correct context.
		$newHeader = function() use( $user ) {
			$workflow = Workflow::create( 'discussion', Title::newMainPage() );
			return array(
				'workflow' => $workflow,
				'revision' => Header::create( $workflow, $user, 'header content', 'wikitext' ),
			);
		};
		$freshTopic = function() use( $user ) {
			$workflow = Workflow::create( 'topic', Title::newMainPage() );
			return array(
				'workflow' => $workflow,
				'revision' => PostRevision::create( $workflow, $user, 'some content', 'wikitext' ),
			);
		};
		$replyToTopic = function() use( $freshTopic, $user ) {
			$metadata = $freshTopic();
			return array(
				'revision' => $metadata['revision']->reply( $metadata['workflow'], $user, 'ffuts dna ylper', 'wikitext' ),
			) + $metadata;
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
				function() use( $freshTopic, $user, $title ) {
					$metadata = $freshTopic();

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
				function() use( $replyToTopic, $user, $title ) {
					$metadata = $replyToTopic();
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
				function() use ( $newHeader, $user, $title ) {
					$metadata = $newHeader();
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
