<?php

namespace Flow\Tests;

use Flow\Data\BagOStuff\LocalBufferedBagOStuff;
use Flow\Data\BufferedCache;
use HashBagOStuff;
use ObjectCache;

/**
 * Runs the exact same set of tests as BufferedBagOStuffTest, but with a
 * LocalBufferedCache object (with static expiry time)
 * @group Flow
 */
class BufferedCacheTest extends BufferedBagOStuffTest {
	protected function setUp() {
		parent::setUp();

		// type defined through parameter
		if ( $this->getCliArg( 'use-bagostuff' ) ) {
			$name = $this->getCliArg( 'use-bagostuff' );

			$this->cache = ObjectCache::newFromId( $name );
		} else {
			// no type defined - use simple hash
			$this->cache = new HashBagOStuff;
		}

		$cache = new LocalBufferedBagOStuff( $this->cache );
		$this->bufferedCache = new BufferedCache( $cache, 30 );
		$this->bufferedCache->begin();
	}
}
