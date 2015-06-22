<?php

namespace Flow\Tests\Data\Storage;

use Flow\Container;
use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Data\Storage\PostRevisionStorage;
use Flow\Model\UUID;
use Flow\Tests\FlowTestCase;

/**
 * @group Flow
 */
class RevisionStorageTest extends FlowTestCase {
	protected $BEFORE_WITHOUT_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://345',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'devwiki',
	);

	protected $AFTER_WITHOUT_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://345',
		'rev_content' => 'Hello, world!',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'testwiki',
	);

	protected $WITHOUT_CONTENT_CHANGE_DIFF = array(
		'rev_mod_user_wiki' => 'testwiki',
	);

	protected $BEFORE_WITH_CONTENT_CHANGE = array(
		'rev_content_url' => 'FlowMock://249',
		'rev_content' => 'Hello, world!<span onclick="alert(\'Hacked\');">Test</span>',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'devwiki',
	);

	protected $AFTER_WITH_CONTENT_CHANGE = array(
		// URL is deliberately stale here; since the column diff shows a content
		// change, processExternalContent is in charge of updating the URL.
		'rev_content_url' => 'FlowMock://249',
		'rev_content' => 'Hello, world!<span>Test</span>',
		'rev_type' => 'reply',
		'rev_mod_user_wiki' => 'devwiki',
	);

	protected $WITH_CONTENT_CHANGE_DIFF = array(
		'rev_content' => 'FlowMock://1',
		'rev_flags' => 'external',
	);

	protected $MOCK_EXTERNAL_STORE_CONFIG = array(
		'FlowMock://',
	);

	public function setup() {
		$this->setMwGlobals( array(
			'wgExternalStores' => array( 'FlowMock' ),
		) );

		\ExternalStoreFlowMock::$isUsed = false;

		parent::setUp();
	}

	public function testCalcUpdatesWithoutContentChangeWhenAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( true );

		$diff = $revStorage->calcUpdates( $this->BEFORE_WITHOUT_CONTENT_CHANGE, $this->AFTER_WITHOUT_CONTENT_CHANGE );

		$this->assertSame(
			false,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, but there is no content change, ExternalStoreFlowMock is untouched'
		);

		$this->assertSame(
			$this->WITHOUT_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are allowed, but there is no content change, content columns are not included in the diff'
		);
	}

	public function testCalcUpdatesWithContentChangeWhenAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( true );

		$diff = $revStorage->calcUpdates( $this->BEFORE_WITH_CONTENT_CHANGE, $this->AFTER_WITH_CONTENT_CHANGE );

		$this->assertSame(
			true,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, and there is a content change, an ExternalStoreFlowMock is constructed'
		);

		$this->assertSame(
			$this->WITH_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are allowed, and there is a content change, the diff shows the updated URL'
		);

	}

	public function testCalcUpdatesWithoutContentChangeWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );

		$diff = $revStorage->calcUpdates( $this->BEFORE_WITHOUT_CONTENT_CHANGE, $this->AFTER_WITHOUT_CONTENT_CHANGE );

		$this->assertSame(
			false,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are not allowed, and there is no content change, ExternalStoreFlowMock is untouched'
		);

		$this->assertSame(
			$this->WITHOUT_CONTENT_CHANGE_DIFF,
			$diff,
			'When content changes are not allowed, and there is no content change, content columns are not included in the diff'
		);
	}

	/**
	 * @expectedException \Flow\Exception\DataModelException
	 */
	public function testCalcUpdatesWithContentChangeWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );

		$revStorage->calcUpdates( $this->BEFORE_WITH_CONTENT_CHANGE, $this->AFTER_WITH_CONTENT_CHANGE );
	}

	public function testUpdatingContentWhenAllowed() {
		$this->helperToTestUpdating(
			$this->BEFORE_WITH_CONTENT_CHANGE,
			$this->AFTER_WITH_CONTENT_CHANGE,
			$this->WITH_CONTENT_CHANGE_DIFF,
			true
		);

		$this->assertSame(
			true,
			\ExternalStoreFlowMock::$isUsed,
			'When content changes are allowed, and there is a content change, an ExternalStoreFlowMock is constructed'
		);
	}

	/**
	 * @expectedException \Flow\Exception\DataModelException
	 */
	public function testUpdatingContentWhenNotAllowed() {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( false );
		$revStorage->update(
			$this->BEFORE_WITH_CONTENT_CHANGE,
			$this->AFTER_WITH_CONTENT_CHANGE
		);
	}

	// A rev ID will be added to $old and $new automatically.
	protected function helperToTestUpdating( $old, $new, $expectedUpdateValues, $isContentUpdatingAllowed ) {
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
		$storage = new HeaderRevisionStorage(
			$factory,
			$this->MOCK_EXTERNAL_STORE_CONFIG
		);

		$this->setWhetherContentUpdatingAllowed( $storage, $isContentUpdatingAllowed );
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
	 * @dataProvider isUpdatingExistingRevisionContentAllowedProvider
	 */
	public function testIsUpdatingExistingRevisionContentAllowed( $shouldContentUpdatesBeAllowed ) {
		$revStorage = $this->getRevisionStorageWithMockExternalStore( $shouldContentUpdatesBeAllowed );

		$this->assertSame(
			$shouldContentUpdatesBeAllowed,
			$revStorage->isUpdatingExistingRevisionContentAllowed()
		);
	}

	/**
	 * @param bool $allowContentUpdates True to allow content updates to existing revisions
	 *
	 * @return HeaderRevisionStorage
	 */
	protected function getRevisionStorageWithMockExternalStore( $allowContentUpdates) {
		$revisionStorage = new HeaderRevisionStorage(
			Container::get( 'db.factory' ),
			$this->MOCK_EXTERNAL_STORE_CONFIG
		);

		$this->setWhetherContentUpdatingAllowed( $revisionStorage, $allowContentUpdates );
		return $revisionStorage;
	}

	protected function setWhetherContentUpdatingAllowed( $revisionStorage, $allowContentUpdates) {
		$klass = new \ReflectionClass( 'Flow\Data\Storage\HeaderRevisionStorage' );
		$allowedUpdateColumnsProp = $klass->getProperty( 'allowedUpdateColumns' );
		$allowedUpdateColumnsProp->setAccessible( true );

		$allowedUpdateColumns = $allowedUpdateColumnsProp->getValue( $revisionStorage );

		$requiredContentColumns = array(
			'rev_content',
			'rev_content_length',
			'rev_flags',
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
		$this->helperToTestUpdating(
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

	public static function issuesQueryCountProvider() {
		return array(
			array(
				'Query by rev_id issues one query',
				// db queries issued
				1,
				// queries
				array(
					array( 'rev_id' => 1 ),
					array( 'rev_id' => 8 ),
					array( 'rev_id' => 3 ),
				),
				// query options
				array( 'LIMIT' => 1 )
			),

			array(
				'Query by rev_id issues one query with string limit',
				// db queries issued
				1,
				// queries
				array(
					array( 'rev_id' => 1 ),
					array( 'rev_id' => 8 ),
					array( 'rev_id' => 3 ),
				),
				// query options
				array( 'LIMIT' => '1' )
			),

			array(
				'Query for most recent revision issues two queries',
				// db queries issued
				2,
				// queries
				array(
					array( 'rev_type_id' => 19 ),
					array( 'rev_type_id' => 22 ),
					array( 'rev_type_id' => 4 ),
					array( 'rev_type_id' => 44 ),
				),
				// query options
				array( 'LIMIT' => 1, 'ORDER BY' => array( 'rev_id DESC' ) ),
			),

		);
	}

	/**
	 * @dataProvider issuesQueryCountProvider
	 */
	public function testIssuesQueryCount( $msg, $count, $queries, $options ) {
		if ( !isset( $options['LIMIT'] ) || $options['LIMIT'] != 1 ) {
			$this->fail( 'Can only generate result set for LIMIT = 1' );
		}
		if ( count( $queries ) <= 2 && count( $queries ) != $count ) {
			$this->fail( '<= 2 queries always issues the same number of queries' );
		}

		$result = array();
		foreach ( $queries as $query ) {
			// this is not in any way a real result, but enough to get through
			// the result processing
			$result[] = (object)( $query + array( 'rev_id' => 42, 'tree_rev_id' => 42, 'rev_flags' => '' ) );
		}

		$treeRepo = $this->getMockBuilder( 'Flow\Repository\TreeRepository' )
			->disableOriginalConstructor()
			->getMock();
		$factory = $this->mockDbFactory();
		// this expect is the assertion for the test
		$factory->getDB( null )->expects( $this->exactly( $count ) )
			->method( 'select' )
			->will( $this->returnValue( $result ) );

		$storage = new PostRevisionStorage( $factory, false, $treeRepo );

		$storage->findMulti( $queries, $options );
	}

	public function testPartialResult() {
		$treeRepo = $this->getMockBuilder( 'Flow\Repository\TreeRepository' )
			->disableOriginalConstructor()
			->getMock();
		$factory = $this->mockDbFactory();
		$factory->getDB( null )->expects( $this->once() )
			->method( 'select' )
			->will( $this->returnValue( array(
				(object)array( 'rev_id' => 42, 'rev_flags' => '' )
			) ) );

		$storage = new PostRevisionStorage( $factory, false, $treeRepo );

		$res = $storage->findMulti(
			array(
				array( 'rev_id' => 12 ),
				array( 'rev_id' => 42 ),
				array( 'rev_id' => 17 ),
			),
			array( 'LIMIT' => 1 )
		);

		$this->assertSame(
			array(
				null,
				array( array( 'rev_id' => 42, 'rev_flags' => '', 'rev_content_url' => null ) ),
				null,
			),
			$res,
			'Unfound items must be represented with null in the result array'
		);
	}

	protected function mockDbFactory() {
		$dbw = $this->getMockBuilder( 'DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();

		$factory = $this->getMock( 'Flow\DbFactory' );
		$factory->expects( $this->any() )
			->method( 'getDB' )
			->will( $this->returnValue( $dbw ) );

		return $factory;
	}
}
