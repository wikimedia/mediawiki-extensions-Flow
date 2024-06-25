<?php

namespace Flow\Tests;

use Flow\Container;
use Flow\TalkpageManager;
use MediaWiki\MainConfigNames;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;
use WikitextContent;

/**
 * @covers \Flow\TalkpageManager
 *
 * @group Flow
 * @group Database
 */
class TalkpageManagerTest extends MediaWikiIntegrationTestCase {
	/**
	 * @var TalkpageManager
	 */
	private $talkpageManager;

	protected function setUp(): void {
		parent::setUp();
		$this->talkpageManager = Container::get( 'occupation_controller' );
	}

	public function testCheckIfCreationIsPossible() {
		$existentTitle = Title::newFromText( 'Exists' );
		$status = $this->getServiceContainer()->getWikiPageFactory()->newFromTitle( $existentTitle )
			->doUserEditContent(
				new WikitextContent( 'This exists' ),
				$this->getTestUser()->getUser(),
				"with an edit summary"
			);
		if ( !$status->isGood() ) {
			$this->fail( $status->getMessage()->plain() );
		}

		$existTrueStatus = $this->talkpageManager->checkIfCreationIsPossible( $existentTitle, /*mustNotExist*/ true );
		$this->assertStatusError( 'flow-error-allowcreation-already-exists', $existTrueStatus,
			'Error when page already exists and mustNotExist true was passed' );

		$existFalseStatus = $this->talkpageManager->checkIfCreationIsPossible( $existentTitle, /*mustNotExist*/ false );
		$this->assertStatusGood( $existFalseStatus,
			'No error when page already exists and mustNotExist false was passed' );
	}

	public function testCheckIfUserHasPermission() {
		global $wgNamespaceContentModels;

		$tempModels = $wgNamespaceContentModels;
		$tempModels[NS_USER_TALK] = CONTENT_MODEL_FLOW_BOARD;

		$unconfirmedUser = User::newFromName( 'UTFlowUnconfirmed' );

		$this->setMwGlobals( [
			'wgFlowReadOnly' => false,
		] );

		$this->overrideConfigValues( [
			MainConfigNames::NamespaceContentModels => $tempModels,
		] );

		$permissionStatus = $this->talkpageManager->checkIfUserHasPermission(
			Title::newFromText( 'User talk:Test123' ), $unconfirmedUser );
		$this->assertStatusGood( $permissionStatus,
			'No error when enabling Flow board in default-Flow namespace' );

		$permissionStatus = $this->talkpageManager->checkIfUserHasPermission(
			Title::newFromText( 'User:Test123' ), $unconfirmedUser );
		$this->assertStatusError( 'flow-error-allowcreation-flow-create-board', $permissionStatus,
			'Correct error thrown when user does not have flow-create-board right' );

		$adminUser = $this->getTestUser( [ 'sysop', 'flow-bot' ] )->getUser();
		$permissionStatus = $this->talkpageManager->checkIfUserHasPermission(
			Title::newFromText( 'User:Test123' ), $adminUser );
		$this->assertStatusGood( $permissionStatus,
			'No error when user with flow-create-board enables Flow board in non-default-Flow namespace' );
	}
}
