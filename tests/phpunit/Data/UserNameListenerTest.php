<?php

namespace Flow\Tests\Data;

use Flow\Data\Listener\UserNameListener;
use Flow\Repository\UserNameBatch;
use Flow\Tests\FlowTestCase;
use ReflectionClass;
use WikiMap;

/**
 * @covers \Flow\Data\Listener\AbstractListener
 * @covers \Flow\Data\Listener\UserNameListener
 *
 * @group Database
 * @group Flow
 */
class UserNameListenerTest extends FlowTestCase {

	public function onAfterLoadDataProvider() {
		return [
			[ [ 'user_id' => '1', 'user_wiki' => 'frwiki' ], [ 'user_id' => 'user_wiki' ], 'frwiki', 'enwiki' ],
			[ [ 'user_id' => '2' ], [ 'user_id' => null ], 'enwiki', 'enwiki' ],
			[ [ 'user_id' => '3' ], [ 'user_id' => 'user_wiki' ], null ],
			// Use closure because WikiMap::getCurrentWikiId() in testxxx() functions appends -unittest_ at the end
			[ [ 'user_id' => '4' ], [ 'user_id' => null ],
				static function () {
					return WikiMap::getCurrentWikiId();
				}
			],
		];
	}

	/**
	 * @dataProvider onAfterLoadDataProvider
	 */
	public function testOnAfterLoad( array $row, array $key, $expectedWiki, $defaultWiki = null ) {
		$batch = new UserNameBatch( $this->createMock( \Flow\Repository\UserName\UserNameQuery::class ) );
		$listener = new UserNameListener( $batch, $key, $defaultWiki );
		$listener->onAfterLoad( (object)$row, $row );

		$reflection = new ReflectionClass( $batch );
		$prop = $reflection->getProperty( 'queued' );
		$prop->setAccessible( true );
		$queued = $prop->getValue( $batch );

		if ( is_callable( $expectedWiki ) ) {
			$expectedWiki = $expectedWiki();
		}

		if ( $expectedWiki ) {
			$this->assertContains( (int)$row['user_id'], $queued[$expectedWiki] );
		} else {
			$this->assertSame( [], $queued );
		}
	}

}
