<?php

use Flow\FlowMessagePoster;

// It's an ApiTestCase since that allows us to check the success case without doing our own
// DB calls.
/**
 * Tests for FlowMessagePoster
 *
 * @group Database
 * @group medium
 *
 * @covers Flow\FlowMessagePoster
 */
class FlowMessagePosterTest extends ApiTestCase {
	protected $talkPageTitle;

	protected $messagePoster;

	protected $sysop;

	function __construct( $name = null, array $data = [], $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		$this->tablesUsed = array_merge(
			$this->tablesUsed,
			[
				'flow_workflow',
				'flow_topic_list',
				'flow_tree_revision',
				'flow_revision',
				'flow_tree_node',
				'ipblocks',
				'logging',
				'page',
				'protected_titles',
				'revision',
				'text',
				'user_groups'
			]
		);
	}

	protected function setUp() {
		parent::setUp();

		$this->setMwGlobals( [
			'wgNamespaceContentModels' => [ NS_TALK => CONTENT_MODEL_FLOW_BOARD ],
		] );

		$this->talkPageTitle = Title::newFromText( 'Talk:FlowMessagePoster' );

		$this->messagePoster = new FlowMessagePoster( $this->talkPageTitle );

		$this->sysop = $this->getTestSysop()->getUser();
	}

	public function testSuccessfulPost() {
		$normalUser = $this->getTestUser()->getUser();
		$this->messagePoster->postTopic( $this->talkPageTitle, $normalUser, 'Subject', 'Body text' );

		$data = $this->doApiRequest(
			array(
				'action' => 'flow',
				'page' => $this->talkPageTitle->getPrefixedText(),
				'submodule' => 'view-topiclist',
				'vtllimit' => 2,
				'vtlsortby' => 'updated',
				'vtlformat' => 'wikitext',
			)
		);

		$response = $data[0]['flow']['view-topiclist']['result']['topiclist'];

		$this->assertSame(
			1,
			count( $response['roots'] ),
			'Sanity check that there is exactly one topic since this is a new board'
		);

		$topicId = $response['roots'][0];

		$rootRevisionId = $response['posts'][$topicId][0];
		$rootRevision = $response['revisions'][$rootRevisionId];

		$this->assertSame(
			$normalUser->getName(),
			$rootRevision['creator']['name'],
			'Post is attributed to correct user'
		);

		$this->assertSame(
			'Subject',
			$rootRevision['content']['content'],
			'Posted topic has correct topic title'
		);

		$bodyRevision = end( $response['revisions'] );

		$this->assertSame(
			$topicId,
			$bodyRevision['replyToId'],
			'Body post is a reply to the topic title'
		);

		$this->assertSame(
			'Body text',
			$bodyRevision['content']['content'],
			'Body post has expected content'
		);
	}

	/**
	 * @expectedException MWException
	 * @expectedExceptionMessage Your username or IP address has been blocked.
	 */
	public function testBlockedUserPost() {
		$blockedUser = $this->getMutableTestUser()->getUser();
		$block = new Block();
		$block->setTarget( $blockedUser );
		$block->setBlocker( $this->sysop );
		$block->mReason = 'Test';
		$block->mExpiry = 'infinity';
		$block->prevents( 'editownusertalk', false );
		$block->insert();

		$this->messagePoster->postTopic( $this->talkPageTitle, $blockedUser, 'Subject', 'Body text' );
	}

	/**
	 * @expectedException MWException
	 * @expectedExceptionMessage This title has been protected from creation by [[User:UTSysop|UTSysop]].
	 */
	public function testProtectedPagePost() {
		$normalUser = $this->getTestUser()->getUser();
		$talkPage = WikiPage::factory( $this->talkPageTitle );
		$cascade = true;
		$talkPage->doUpdateRestrictions( [ 'create' => 'sysop' ], [ 'infinity' ], $cascade, 'Test', $this->sysop );

		$this->messagePoster->postTopic( $this->talkPageTitle, $normalUser, 'Subject', 'Body text' );
	}
}
