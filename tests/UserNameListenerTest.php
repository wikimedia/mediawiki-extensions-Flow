<?php

namespace Flow\Tests;

use Closure;
use ReflectionClass;
use Flow\Container;
use Flow\Data\UserNameBatch;
use Flow\Data\UserNameListener;

/**
 * @group Database
 * @group Flow
 */
class UserNameListenerTest extends \MediaWikiTestCase {

	public function onAfterLoadDataProvider() {
		return array (
			array( array( 'user_id' => '1', 'user_wiki' => 'frwiki' ), array( 'user_id' => 'user_wiki' ), 'frwiki', 'enwiki' ),
			array( array( 'user_id' => '2' ), array( 'user_id' => null ), 'enwiki', 'enwiki' ),
			array( array( 'user_id' => '3' ), array( 'user_id' => 'user_wiki' ), null ),
			// Use closure because wfWikiId() in testxxx() functions appends -unittest_ at the end
			array( array( 'user_id' => '4' ), array( 'user_id' => null ), function() { return wfWikiId(); } ),
		);
	}

	/**
	 * @dataProvider onAfterLoadDataProvider
	 */
	public function testOnAfterLoad( array $row, array $key, $expectedWiki, $defaultWiki = null ) {
		$batch = new UserNameBatch( $this->getMock( '\Flow\Data\UsernameQuery' ) );
		$listener = new UserNameListener( $batch, $key, $defaultWiki );
		$listener->onAfterLoad( (object)$row, $row );

		$reflection = new ReflectionClass( $batch );
		$prop = $reflection->getProperty( 'queued' );
		$prop->setAccessible( true );
		$queued = $prop->getValue( $batch );

		if ( $expectedWiki instanceof Closure ) {
			$expectedWiki = call_user_func( $expectedWiki );
		}

		if ( $expectedWiki ) {
			$this->assertTrue( in_array( $row['user_id'], $queued[$expectedWiki] ) );
		} else {
			$this->assertEmpty( $queued );
		}
	}

}
