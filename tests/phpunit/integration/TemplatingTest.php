<?php

namespace Flow\Tests;

use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use Flow\Parsoid\ContentFixer;
use Flow\Repository\UserName\UserNameQuery;
use Flow\Repository\UserNameBatch;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use MediaWikiIntegrationTestCase;
use Title;
use User;

/**
 * @covers \Flow\Templating
 *
 * @group Flow
 */
class TemplatingTest extends MediaWikiIntegrationTestCase {

	private function mockTemplating(): Templating {
		return new Templating(
			new UserNameBatch( $this->createMock( UserNameQuery::class ) ),
			$this->createMock( ContentFixer::class ),
			$this->createMock( RevisionActionPermissions::class )
		);
	}

	/**
	 * There was a bug where all anonymous users got the same
	 * user links output, this checks that they are distinct.
	 */
	public function testNonRepeatingUserLinksForAnonymousUsers() {
		$templating = $this->mockTemplating();

		$user = User::newFromName( '127.0.0.1', false );
		$title = Title::newMainPage();
		$workflow = Workflow::create( 'topic', $title );
		$topicTitle = PostRevision::createTopicPost( $workflow, $user, 'some content' );

		$hidden = $topicTitle->moderate(
			$user,
			$topicTitle::MODERATED_HIDDEN,
			'hide-topic',
			'hide and go seek'
		);

		$this->assertStringContainsString(
			'Special:Contributions/127.0.0.1',
			$templating->getUserLinks( $hidden ),
			'User links should include anonymous contributions'
		);

		$hidden = $topicTitle->moderate(
			User::newFromName( '10.0.0.2', false ),
			$topicTitle::MODERATED_HIDDEN,
			'hide-topic',
			'hide and go seek'
		);
		$this->assertStringContainsString(
			'Special:Contributions/10.0.0.2',
			$templating->getUserLinks( $hidden ),
			'An alternate user should have the correct anonymous contributions'
		);
	}
}
