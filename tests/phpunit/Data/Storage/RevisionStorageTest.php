<?php

namespace Flow\Tests\Data\Storage;

use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Data\Storage\RevisionStorage;
use Flow\Model\UUID;
use Flow\Container;

/**
 * @group Flow
 */
class RevisionStorageTest extends \MediaWikiTestCase {
	protected $BEFORE_WITHOUT_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://345',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_user_wiki', 'devwiki',
	);

	protected $AFTER_WITHOUT_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://345',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_user_wiki', 'testwiki',
	);

	protected $BEFORE_WITH_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://249',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_user_wiki', 'devwiki',
	);

	protected $AFTER_WITH_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://249', // Deliberately stale here
		'rev_content' => 'Hello, moon!',
		'rev_type' => 'reply',
		'rev_user_wiki', 'devwiki',
	);

	public function setup() {
		$this->setMwGlobals( array(
			'wgExternalStores' => array(
				'default' => array( 'FlowMock' ),
			),
		) );
	}

	public function testCalcUpdatesWithoutContentChangeWhenAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( true );

		/* $before = */

		/* $this->assertSame( */
		/* 	'rev_content' => */
	}

	public function testCalcUpdatesWithContentChangeWhenAllowed() {

	}

	public function testCalcUpdatesWithoutContentChangeWhenNotAllowed() {

	}

	public function testCalcUpdatesWithContentChangeWhenNotAllowed() {

	}

	protected function getRevisionStorageAllowingContentUpdates() {

	}

	/**
	 * @param bool $allowContentUpdates True to allow content updates to existing revisions
	 *
	 * @return HeaderRevisionStorage
	 */
	protected function getRevisionStorageWithMockExternalStore( $allowContentUpdates) {
		ExternalStoreFlowMock::$isUsed = false;

		$revisionStorage = new HeaderRevisionStorage(
			Container::get( 'db.factory' ),
			array(
				'FlowMock://',
			)
		);

		$allowedUpdateColumnsProp = ReflectionClass::getProperty( 'allowedUpdateColumns' );
		$allowedUpdateColumnsProp->setAccessible( true );

		$allowedUpdateColumns = $allowedUpdateColumnsProp->getValue( $revisionStorage );

		$requiredContentColumns = array(
			'rev_content',
			'rev_content_length',
			'rev_previous_content_length',
		);

		if ( $allowContentUpdates ) {
			$allowedUpdateColumns = array_merge(
				$allowedUpdateColumns,
				$requiredContentColumns
			);

			$allowedUpdateColumns = array_unique( $allowedUpdateColumns );
		} else {
			$allowedUpdateColumns = array_diff( $allowedUpdateColumns, $requiredContentColumns );
		}

		$allowedUpdateColumnsProp->setValue( $revisionStorage, $allowedUpdateColumns );
		return $revisionStorage;
	}

	public function testUpdateConvertsPrimaryKeyToBinary() {
		$dbw = $this->getMockBuilder( 'DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();
		$factory = $this->getMockBuilder( 'Flow\DbFactory' )
			->disableOriginalConstructor()
			->getMock();
		$factory->expects( $this->any() )
			->method( 'getDB' )
			->will( $this->returnValue( $dbw ) );

		$id = UUID::create();
		$dbw->expects( $this->once() )
			->method( 'update' )
			->with(
				$this->equalTo( 'flow_revision' ),
				$this->equalTo( array(
					'rev_mod_user_id' => 42,
				) ),
				$this->equalTo( array(
					'rev_id' => $id->getBinary(),
				) )
			)
			->will( $this->returnValue( true ) );
		$dbw->expects( $this->any() )
			->method( 'affectedRows' )
			->will( $this->returnValue( 1 ) );

		// Header is bare bones implementation, sufficient for testing
		// the parent class.
		$storage = new HeaderRevisionStorage( $factory, /* $externalStore = */false );
		$storage->update(
			array(
				'rev_id' => $id->getAlphadecimal(),
				'rev_mod_user_id' => 0,
			),
			array(
				'rev_id' => $id->getAlphadecimal(),
				'rev_mod_user_id' => 42,
			)
		);

	}
}
