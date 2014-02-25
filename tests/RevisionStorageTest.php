<?php

namespace Flow\Tests;

use Flow\Data\PostRevisionStorage;
use Flow\Data\ObjectManager;

class RevisionStorageTest extends \MediaWikiTestCase {

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
					array( 'tree_rev_descendant_id' => 19 ),
					array( 'tree_rev_descendant_id' => 22 ),
					array( 'tree_rev_descendant_id' => 4 ),
					array( 'tree_rev_descendant_id' => 44 ),
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
