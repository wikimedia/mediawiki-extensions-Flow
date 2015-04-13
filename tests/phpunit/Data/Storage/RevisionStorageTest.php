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

	protected $WITHOUT_CONTENT_CHANGE_DIFF = array(
		'rev_user_wiki' => 'testwiki',
	);

	protected $BEFORE_WITH_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://249',
		'rev_content' => 'Hello, world!<span onclick="alert(\'Hacked\');">Test</span>',
		'rev_type' => 'reply',
		'rev_user_wiki', 'devwiki',
	);

	protected $AFTER_WITH_CONTENT_CHANGE = array(
		// URL is deliberately stale here; since the column diff shows a content
		// change, processExternalContent is in charge of updating the URL.
		'rev_content_url' => 'FlowMock://249',
		'rev_content' => 'Hello, world!<span>Test</span>',
		'rev_type' => 'reply',
		'rev_user_wiki', 'devwiki',
	);

	protected $WITH_CONTENT_CHANGE_DIFF = array(
		'rev_content' => 'FlowMock://1',
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

		$diff = $revStorage->calcUpdates( $BEFORE_WITHOUT_CONTENT_CHANGE, $AFTER_WITHOUT_CONTENT_CHANGE );

		$this->assertSame(
			false,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, but there is no content change, ExternalStoreFlowMock is untouched'
		);

		$this->assertSame(
			self::$WITHOUT_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are allowed, but there is no content change, content columns are not included in the diff'
		);
	}

	public function testCalcUpdatesWithContentChangeWhenAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( true );

		$diff = $revStorage->calcUpdates( $BEFORE_WITH_CONTENT_CHANGE, $AFTER_WITH_CONTENT_CHANGE );

		$this->assertSame(
			true,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, and there is a content change, an ExternalStoreFlowMock is constructed'
		);

		$this->assertSame(
			self::$WITH_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are allowed, and there is a content change, the diff shows the updated URL'
		);

	}

	public function testCalcUpdatesWithoutContentChangeWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );

		$diff = $revStorage->calcUpdates( $BEFORE_WITHOUT_CONTENT_CHANGE, $AFTER_WITHOUT_CONTENT_CHANGE );

		$this->assertSame(
			false,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are not allowed, and there is no content change, ExternalStoreFlowMock is untouched'
		);

		$this->assertSame(
			self::$WITHOUT_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are not allowed, and there is no content change, content columns are not included in the diff'
		);
	}

	/**
	 * @expectedException Flow\Exception\DataModelException
	 */
	public function testCalcUpdatesWithContentChangeWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );

		$diff = $revStorage->calcUpdates( $BEFORE_WITH_CONTENT_CHANGE, $AFTER_WITH_CONTENT_CHANGE );
	}

	/**
	   @provider isUpdatingExistingRevisionContentAllowedProvider
	*/
	public function testIsUpdatingExistingRevisionContentAllowed( $shouldBeAllowed ) {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( $allowContentUpdates );

		$this->assertSame(
			$shouldBeAllowed,
			$revStorage->isUpdatingExistingRevisionContentAllowed()
		);
	}

	protected function testUpdatingContentWhenAllowed() {
		$this->testUpdating(
			self::$BEFORE_WITH_CONTENT_CHANGE,
			self::$AFTER_WITH_CONTENT_CHANGE,
			$WITH_CONTENT_CHANGE_DIFF,
			true
		);
	}

	/**
	 * @expectedException Flow\Exception\DataModelException
	 */
	protected function testUpdatingContentWhenNotAllowed() {
		$this->testUpdating(
			self::$BEFORE_WITHOUT_CONTENT_CHANGE,
			self::$AFTER_WITHOUT_CONTENT_CHANGE,
			$WITHOUT_CONTENT_CHANGE_DIFF,
			false
		);
	}

	// A rev ID will be added to $old and $new automatically.
	protected function testUpdating( $old, $new, $expectedUpdateValues, $isContentUpdatingAllowed ) {
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

		$old['rev_id'] = $id->getBinary();
		$new['rev_id'] = $id->getBinary();

		$dbw->expects( $this->once() )
			->method( 'update' )
			->with(
				$this->equalTo( 'flow_revision' ),
				$this->equalTo( $expectedUpdateValues ),
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
		$this->markWhetherContentUpdatingAllowed( $storage, $isContentUpdatingAllowed );
		$storage->update(
			$old,
			$new
		);

	}

	public function isUpdatingExistingRevisionContentAllowedProvider() {
		return array(
			array( true ),
			array( false )
		);
	}

	/**
	 * @param bool $allowContentUpdates True to allow content updates to existing revisions
	 *
	 * @return HeaderRevisionStorage
	 */
	protected function getRevisionStorageWithMockExternalStore( $allowContentUpdates) {
		\ExternalStoreFlowMock::$isUsed = false;

		$revisionStorage = new HeaderRevisionStorage(
			Container::get( 'db.factory' ),
			array(
				'FlowMock://',
			)
		);

		$this->markWhetherContentUpdatingAllowed( $revisionStorage, $allowContentUpdates );
		return $revisionStorage;
	}

	protected function markWhetherContentUpdatingAllowed( $revisionStorage, $allowContentUpdates) {
		$allowedUpdateColumnsProp = \ReflectionClass::getProperty( 'allowedUpdateColumns' );
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
	}

	public function testUpdateConvertsPrimaryKeyToBinary() {
		$this->testUpdating(
			array(
				'rev_mod_user_id' => 0,
			),
			array(
				'rev_mod_user_id' => 42,
			),
			array(
				'rev_mod_user_id' => 42,
			),
			false
		);
	}
}
