<?php

namespace Flow\Tests\Data\Storage;

use Flow\Data\Storage\HeaderRevisionStorage;
use Flow\Model\UUID;

/**
 * @group Flow
 */
class RevisionStorageTest extends \MediaWikiTestCase {

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
					'rev_id' => wfGetDB( DB_SLAVE )->encodeBlob( $id->getBinary() ),
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
