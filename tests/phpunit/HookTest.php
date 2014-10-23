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
class HookTest extends FlowTestCase {
	static public function onIRCLineURLProvider() {
		$user = User::newFromName( '127.0.0.1', false );
		$title = Title::newMainPage();
		$boardWorkflow = Workflow::create( 'discussion', $user, Title::newMainPage() );
		$workflow = Workflow::create( 'topic', $user, Title::newMainPage() );
		$topicTitle = PostRevision::create( $workflow, 'some content' );
		$firstReply = $topicTitle->reply( $workflow, $user, 'ffuts dna ylper' );
		$header = Header::create( $workflow, $user, 'header content' );

		$titleText = $workflow->getArticleTitle()->getPrefixedText();

		return array(
			array(
				// test message
				'Freshly created topic',
				// flow-workflow-change attribute within rc_params
				$workflow,
				$topicTitle,
				// expected query parameters
				array(
					'title' => $titleText,
					'action' => 'history',
				),
			),

			array(
				'Reply to topic',
				$workflow,
				$firstReply,
				array(
					'title' => $titleText,
					'action' => 'history',
				),
			),

			array(
				'Edit topic title',
				$workflow,
				$topicTitle->newNextRevision( $user, 'gnihtemos gnihtemos', 'edit-title', $title ),
				array(
					'title' => $titleText,
					'action' => 'compare-post-revisions',
				),
			),

			array(
				'Edit post',
				$workflow,
				$firstReply->newNextRevision( $user, 'IT\'S CAPS LOCKS DAY!', 'edit-post', $title ),
				array(
					'title' => $titleText,
					'action' => 'compare-post-revisions',
				),
			),

			array(
				'Edit board header',
				$boardWorkflow,
				$header->newNextRevision( $user, 'STILL CAPS LOCKS DAY!', 'edit-header', $title ),
				array(
					'title' => 'Main_Page',
					'action' => 'compare-header-revisions',
				),
			),

			array(
				'Moderate a post',
				$workflow,
				$firstReply->moderate(
					$user,
					$firstReply::MODERATED_DELETED,
					'delete-post',
					'something about cruise control'
				),
				array(
					'title' => $titleText,
					'action' => 'history',
				),
			),

			array(
				'Moderate a topic',
				$workflow,
				$topicTitle->moderate(
					$user,
					$topicTitle::MODERATED_HIDDEN,
					'hide-topic',
					'adorable kittens'
				),
				array(
					'title' => $titleText,
					'action' => 'history',
				),
			),
		);
	}

	/**
	 * @dataProvider onIRCLineUrlProvider
	 */
	public function testOnIRCLineUrl( $message, Workflow $workflow, AbstractRevision $revision, $expectedQuery ) {
		$rc = new RecentChange;
		$rc->mAttribs = array(
			'rc_namespace' => 0,
			'rc_title' => 'Main Page',
			'rc_source' => RecentChangesListener::SRC_FLOW,
		);
		Container::get( 'formatter.irclineurl' )->associate( $rc, array(
			'revision' => $revision,
			'workflow' => $workflow,
		) );

		$url = 'unset';
		$query = 'unset';
		$this->assertTrue( FlowHooks::onIRCLineURL( $url, $query, $rc ) );

		$parts = parse_url( $url );
		parse_str( $parts['query'], $queryParts );
		foreach ( $expectedQuery as $key => $value ) {
			$this->assertEquals( $value, $queryParts[$key], "Query part $key" );
		}
		$this->assertEquals( '', $query, $message );
	}
}
