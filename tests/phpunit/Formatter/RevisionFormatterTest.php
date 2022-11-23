<?php

namespace Flow\Tests\Formatter;

use Flow\FlowActions;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Repository\UserNameBatch;
use Flow\RevisionActionPermissions;
use Flow\Templating;
use Flow\Tests\PostRevisionTestCase;
use Flow\UrlGenerator;
use RequestContext;
use Title;
use User;

/**
 * @covers \Flow\Formatter\RevisionFormatter
 * @covers \Flow\Model\AbstractRevision
 * @covers \Flow\Model\PostRevision
 *
 * @group Flow
 * @group Database
 */
class RevisionFormatterTest extends PostRevisionTestCase {
	/** @var User */
	private $user;

	protected function setUp(): void {
		parent::setUp();

		$this->user = User::newFromName( '127.0.0.1', false );

		// These tests don't provide sufficient data to properly run all listeners
		$this->clearExtraLifecycleHandlers();
	}

	/**
	 * @dataProvider decideContentFormatProvider
	 */
	public function testDecideContentFormat(
		string $expectedFormat,
		string $setContentRequestedFormat,
		?UUID $setContentRevisionId,
		AbstractRevision $revision
	) {
		/** @var RevisionFormatter $formatter */
		list( $formatter ) = $this->makeFormatter();
		$formatter->setContentFormat( $setContentRequestedFormat, $setContentRevisionId );

		$this->assertEquals(
			$expectedFormat,
			$formatter->decideContentFormat( $revision )
		);
	}

	public function decideContentFormatProvider() {
		$topicTitleRevisionUnspecified = $this->mockTopicTitleRevision();
		$topicTitleRevisionSpecified = $this->mockTopicTitleRevision();

		$postRevisionUnspecified = $this->mockPostRevision();
		$postRevisionSpecified = $this->mockPostRevision();

		return [
			[
				'topic-title-html',
				'fixed-html',
				null,
				$topicTitleRevisionUnspecified,
			],
			// Specified for a different revision, so uses canonicalized
			// version of class default (fixed-html => topic-title-html).
			[
				'topic-title-html',
				'topic-title-wikitext',
				$topicTitleRevisionSpecified->getRevisionId(),
				$topicTitleRevisionUnspecified,
			],
			[
				'topic-title-wikitext',
				'html',
				null,
				$topicTitleRevisionUnspecified,
			],
			[
				'topic-title-wikitext',
				'wikitext',
				null,
				$topicTitleRevisionUnspecified,
			],
			[
				'fixed-html',
				'fixed-html',
				null,
				$postRevisionUnspecified,
			],
			// We've specified it, but for another rev ID, so it uses the class default
			// of fixed-html.
			[
				'fixed-html',
				'wikitext',
				$postRevisionSpecified->getRevisionId(),
				$postRevisionUnspecified,
			],
			[
				'html',
				'html',
				null,
				$postRevisionUnspecified,
			],
			[
				'wikitext',
				'wikitext',
				null,
				$postRevisionUnspecified,
			],
			[
				'topic-title-html',
				'topic-title-html',
				null,
				$topicTitleRevisionUnspecified,
			],
			[
				'topic-title-wikitext',
				'topic-title-wikitext',
				null,
				$topicTitleRevisionUnspecified,
			],
			[
				'topic-title-html',
				'topic-title-html',
				$topicTitleRevisionSpecified->getRevisionId(),
				$topicTitleRevisionSpecified,
			],
			[
				'topic-title-wikitext',
				'topic-title-wikitext',
				$topicTitleRevisionSpecified->getRevisionId(),
				$topicTitleRevisionSpecified,
			],
			[
				'fixed-html',
				'fixed-html',
				$postRevisionSpecified->getRevisionId(),
				$postRevisionSpecified,
			],
			[
				'html',
				'html',
				$postRevisionSpecified->getRevisionId(),
				$postRevisionSpecified,
			],
			[
				'wikitext',
				'wikitext',
				$postRevisionSpecified->getRevisionId(),
				$postRevisionSpecified,
			],
		];
	}

	/**
	 * @dataProvider decideContentInvalidFormatProvider
	 */
	public function testDecideContentInvalidFormat( $setContentRequestedFormat, $setContentRevisionId, $revision ) {
		/** @var RevisionFormatter $formatter */
		list( $formatter ) = $this->makeFormatter();
		$formatter->setContentFormat( $setContentRequestedFormat, $setContentRevisionId );
		$this->expectException( \Flow\Exception\FlowException::class );
		$formatter->decideContentFormat( $revision );
	}

	public function decideContentInvalidFormatProvider() {
		$topicTitleRevisionSpecified = $this->mockTopicTitleRevision();
		$postRevisionSpecified = $this->mockPostRevision();
		$postRevisionUnspecified = $this->mockPostRevision();

		return [
			[
				'wikitext',
				$topicTitleRevisionSpecified->getRevisionId(),
				$topicTitleRevisionSpecified,
			],
			[
				'topic-title-html',
				$postRevisionSpecified->getRevisionId(),
				$postRevisionSpecified,
			],
			[
				'topic-title-html',
				null,
				$postRevisionUnspecified,
			],
			[
				'topic-title-wikitext',
				$postRevisionSpecified->getRevisionId(),
				$postRevisionSpecified,
			],
			[
				'topic-title-wikitext',
				null,
				$postRevisionUnspecified,
			],
		];
	}

	/**
	 * @dataProvider setContentFormatInvalidProvider
	 */
	public function testSetContentFormatInvalidProvider( $requestedFormat, $revisionId ) {
		/** @var RevisionFormatter $formatter */
		list( $formatter ) = $this->makeFormatter();
		$this->expectException( \Flow\Exception\FlowException::class );
		$formatter->setContentFormat( $requestedFormat, $revisionId );
	}

	public function setContentFormatInvalidProvider() {
		$postRevisionSpecified = $this->mockPostRevision();

		return [
			[
				'fake-format',
				null
			],
			[
				'another-fake-format',
				$postRevisionSpecified->getRevisionId()
			],
		];
	}

	public function testMockFormatterBasicallyWorks() {
		/** @var RevisionFormatter $formatter */
		list( $formatter, $ctx ) = $this->makeFormatter();
		$result = $formatter->formatApi( $this->generateFormatterRow( 'my new topic' ), $ctx );
		$this->assertEquals( 'new-post', $result['changeType'] );
		$this->assertEquals( 'my new topic', $result['content']['content'] );
	}

	public function testFormattingEditedTitle() {
		/** @var RevisionFormatter $formatter */
		list( $formatter, $ctx ) = $this->makeFormatter();
		$row = $this->generateFormatterRow();
		$row->previousRevision = $row->revision;
		$row->revision = $row->revision->newNextRevision(
			$this->user,
			'replacement content',
			'topic-title-wikitext',
			'edit-title',
			$row->workflow->getArticleTitle()
		);
		$result = $formatter->formatApi( $row, $ctx );
		$this->assertEquals( 'edit-title', $result['changeType'] );
		$this->assertEquals( 'replacement content', $result['content']['content'] );
	}

	public function testFormattingContentLength() {
		$content = 'something something';
		$nextContent = 'ברוכים הבאים לוויקיפדיה!';

		/** @var RevisionFormatter $formatter */
		list( $formatter, $ctx ) = $this->makeFormatter();

		$row = $this->generateFormatterRow( $content );
		$result = $formatter->formatApi( $row, $ctx );
		$this->assertEquals(
			strlen( $content ),
			$result['size']['new'],
			'New topic content reported correctly'
		);
		$this->assertSame(
			0,
			$result['size']['old'],
			'With no previous revision the old size is 0'
		);

		$row->previousRevision = $row->revision;
		// @todo newNextRevision feels too generic, there should be an editTitle method?
		$row->revision = $row->currentRevision = $row->revision->newNextRevision(
			$this->user,
			$nextContent,
			'topic-title-wikitext',
			'edit-title',
			$row->workflow->getArticleTitle()
		);
		$result = $formatter->formatApi( $row, $ctx );
		$this->assertEquals(
			mb_strlen( $nextContent ),
			$result['size']['new'],
			'After editing topic content the new size has been updated'
		);
		$this->assertEquals(
			mb_strlen( $content ),
			$result['size']['old'],
			'After editing topic content the old size has been updated'
		);
	}

	public function generateFormatterRow( $wikitext = 'titlebar content' ) {
		$row = new FormatterRow;

		$row->workflow = Workflow::create( 'topic', Title::newMainPage() );
		$this->workflows[$row->workflow->getId()->getAlphadecimal()] = $row->workflow;

		$row->rootPost = PostRevision::createTopicPost( $row->workflow, $this->user, $wikitext );
		$row->revision = $row->currentRevision = $row->rootPost;
		$this->store( $row->revision );

		return $row;
	}

	private function mockPermissions( FlowActions $actions ): RevisionActionPermissions {
		$permissions = $this->createMock( RevisionActionPermissions::class );
		// formatting only proceedes when this is true
		$permissions->method( 'isAllowed' )
			->willReturn( true );
		// bit of a code smell, should pass actions directly in constructor?
		$permissions->method( 'getActions' )
			->willReturn( $actions );
		// perhaps another code smell, should have a method that does whatever this
		// uses the user for
		$permissions->method( 'getUser' )
			->willReturn( $this->user );

		return $permissions;
	}

	private function mockPostRevision(): PostRevision {
		$postRevision = $this->createMock( PostRevision::class );
		$postRevision->method( 'isTopicTitle' )
			->willReturn( false );
		$postRevision->method( 'getRevisionId' )
			->willReturn( UUID::create() );
		return $postRevision;
	}

	private function mockTemplating(): Templating {
		$templating = $this->createMock( Templating::class );
		$templating->method( 'getModeratedRevision' )
			->willReturnArgument( 0 );
		$templating->method( 'getContent' )
			->willReturnCallback( static function ( $revision, $contentFormat ) {
				return $revision->getContent( $contentFormat );
			} );

		return $templating;
	}

	private function mockTopicTitleRevision(): PostRevision {
		$topicTitleRevision = $this->createMock( PostRevision::class );
		$topicTitleRevision->method( 'isTopicTitle' )
			->willReturn( true );
		$topicTitleRevision->method( 'getRevisionId' )
			->willReturn( UUID::create() );
		return $topicTitleRevision;
	}

	public function makeFormatter(): array {
		$formatter = new RevisionFormatter(
			$this->mockPermissions( $this->createMock( FlowActions::class ) ),
			$this->mockTemplating(),
			$this->createMock( UrlGenerator::class ),
			$this->createMock( UserNameBatch::class ),
			3
		);

		$ctx = RequestContext::getMain();
		$ctx->setUser( $this->user );

		return [ $formatter, $ctx ];
	}
}
