<?php

namespace Flow\Tests\Formatter;

use Flow\FlowActions;
use Flow\Formatter\FormatterRow;
use Flow\Formatter\RevisionFormatter;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use RequestContext;
use Title;
use User;

/**
 * @group Flow
 */
class RevisionFormatterTest extends \MediaWikiTestCase {
	protected $user;

	public function setUp() {
		parent::setUp();
		$this->user = User::newFromName( '127.0.0.1', false );
	}

	public function testMockFormatterBasicallyWorks() {
		list( $formatter, $ctx ) = $this->mockFormatter();
		$result = $formatter->formatApi( $this->generateRow( 'my new topic' ), $ctx );
		$this->assertEquals( 'new-post', $result['changeType'] );
		$this->assertEquals( 'my new topic', $result['content']['content'] );
	}

	public function testFormattingEditedTitle() {
		list( $formatter, $ctx ) = $this->mockFormatter();
		$row = $this->generateRow();
		$row->previousRevision = $row->revision;
		$row->revision = $row->revision->newNextRevision(
			$this->user,
			'replacement content',
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

		list( $formatter, $ctx, $permissions, $templating, $usernames, $actions ) = $this->mockFormatter( true );

		$row = $this->generateRow( $content );
		$result = $formatter->formatApi( $row, $ctx );
		$this->assertEquals(
			strlen( $content ),
			$result['size']['new'],
			'New topic content reported correctly'
		);
		$this->assertEquals(
			0,
			$result['size']['old'],
			'With no previous revision the old size is 0'
		);

		$row->previousRevision = $row->revision;
		// @todo newNextRevision feels too generic, there should be an editTitle method?
		$row->revision = $row->currentRevision = $row->revision->newNextRevision(
			$this->user,
			$nextContent,
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

	public function generateRow( $plaintext = 'titlebar content' ) {
		$row = new FormatterRow;
		$row->workflow = Workflow::create( 'topic', Title::newMainPage() );
		$row->rootPost = PostRevision::create( $row->workflow, $this->user, $plaintext );
		$row->revision = $row->currentRevision = $row->rootPost;

		return $row;
	}

	protected function mockActions() {
		return $this->getMockBuilder( 'Flow\FlowActions' )
			->disableOriginalConstructor()
			->getMock();
	}

	protected function mockPermissions( FlowActions $actions ) {
		$permissions = $this->getMockBuilder( 'Flow\RevisionActionPermissions' )
			->disableOriginalConstructor()
			->getMock();
		// bit of a code smell, should pass actions directly in constructor?
		$permissions->expects( $this->any() )
			->method( 'getActions' )
			->will( $this->returnValue( $actions ) );
		// perhaps another code smell, should have a method that does whatever this
		// uses the user for
		$permissions->expects( $this->any() )
			->method( 'getUser' )
			->will( $this->returnValue( $this->user ) );

		return $permissions;
	}

	protected function mockTemplating() {
		$templating = $this->getMockBuilder( 'Flow\Templating' )
			->disableOriginalConstructor()
			->getMock();
		$templating->expects( $this->any() )
			->method( 'getModeratedRevision' )
			->will( $this->returnArgument( 0 ) );
		$templating->expects( $this->any() )
			->method( 'getContent' )
			->will( $this->returnCallback( function( $revision, $contentFormat ) {
				return $revision->getContent( $contentFormat );
			} ) );

		return $templating;
	}

	protected function mockUserNameBatch() {
		return $this->getMockBuilder( 'Flow\Repository\UserNameBatch' )
			->disableOriginalConstructor()
			->getMock();
	}

	// @todo name seems wrong, the Formatter is real everything else is mocked
	public function mockFormatter( $returnAll = false ) {
		$actions = $this->mockActions();
		$permissions = $this->mockPermissions( $actions );
		// formatting only proceedes when this is true
		$permissions->expects( $this->any() )
			->method( 'isAllowed' )
			->will( $this->returnValue( true ) );
		$templating = $this->mockTemplating();
		$usernames = $this->mockUserNameBatch();
		$formatter = new RevisionFormatter( $permissions, $templating, $usernames, 3 );

		$ctx = RequestContext::getMain();
		$ctx->setUser( $this->user );


		if ( $returnAll ) {
			return array( $formatter, $ctx, $permissions, $templating, $usernames, $actions );
		} else {
			return array( $formatter, $ctx );
		}
	}
}
