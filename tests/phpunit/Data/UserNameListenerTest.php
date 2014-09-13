<?php

namespace Flow\Tests\Data;

use Closure;
use ReflectionClass;
use Flow\Repository\UserNameBatch;
use Flow\Data\Listener\UserNameListener;
use Flow\Tests\FlowTestCase;

/**
 * @group Database
 * @group Flow
 */
class UserNameListenerTest extends FlowTestCase {

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
		$batch = new UserNameBatch( $this->getMock( '\Flow\Repository\UserName\UserNameQuery' ) );
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
