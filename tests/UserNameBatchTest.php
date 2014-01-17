<?php

namespace Flow\Tests;

use Flow\Data\UserNameBatch;
use Flow\Data\UserNameQuery;

/**
 * @group Database
 */
class UserNameBatchTest extends \MediaWikiTestCase {

	public function testAllowsAddingNames() {
		$batch = new UserNameBatch( $this->createUncalledQuery() );
		$batch->add( 'fakewiki', 42, 'Whale' );
		$this->assertEquals( 'Whale', $batch->get( 'fakewiki', 42 ) );
	}

	static public function acceptsStringOrIntIdsProvider() {
		return array(
			array( 42, 42 ),
			array( 42, '42' ),
			array( '42', 42 ),
			array( '42', '42' ),
		);
	}

	/**
	 * @dataProvider acceptsStringOrIntIdsProvider
	 */
	public function testAcceptsStringOrIntIds( $a, $b ) {
		$batch = new UserNameBatch( $this->createUncalledQuery() );
		$batch->add( 'fakewiki', $a, 'Whale' );
		$this->assertEquals( 'Whale', $batch->get( 'fakewiki', $b ) );
	}

	public function testQueueUsernames() {
		$query = $this->getMock( 'Flow\Data\UsernameQuery' );
		$query->expects( $this->once() )
			->method( 'execute' )
			->with( 'fakewiki', array( 12, 27, 18 ) );

		$batch = new UserNameBatch( $query );
		$batch->add( 'fakewiki', 12 );
		$batch->add( 'fakewiki', '27' );
		$batch->add( 'fakewiki', 18 );
		$batch->resolve( 'fakewiki' );
	}

	public function testMissingAsFalse() {
		$query = $this->getMock( 'Flow\Data\UsernameQuery' );
		$query->expects( $this->once() )
			->method( 'execute' )
			->with( 'fakewiki', array( 42 ) );
		$batch = new UserNameBatch( $query );

		$this->assertEquals( false, $batch->get( 'fakewiki', 42 ) );
	}

	public function testPartialMissingAsFalse() {
		$query = $this->getMock( 'Flow\Data\UsernameQuery' );
		$query->expects( $this->once() )
			->method( 'execute' )
			->with( 'fakewiki', array( 610, 408 ) )
			->will( $this->returnValue( array(
				(object)array( 'user_id' => '408', 'user_name' => 'chuck' )
			) ) );

		$batch = new UserNameBatch( $query );
		$batch->add( 'fakewiki', 610 );
		$batch->add( 'fakewiki', 408 );

		$this->assertEquals( false, $batch->get( 'fakewiki', 610 ) );
	}

	/**
	 * Create a mock UsernameQuery that must not be called
	 * @return UsernameQuery
	 */
	protected function createUncalledQuery() {
		$query = $this->getMock( 'Flow\Data\UsernameQuery' );
		$query->expects( $this->never() )
			->method( 'execute' );

		return $query;
	}
}
