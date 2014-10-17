<?php

namespace Flow\Tests;

use Flow\Data\BagOStuff\LocalBufferedBagOStuff;
use HashBagOStuff;
use ObjectCache;

/**
 * Runs the exact same set of tests as BufferedBagOStuffTest, but with a
 * LocalBufferedBagOStuff object (where get requests are also cached)
 * @group Flow
 */
class LocalBufferedBagOStuffTest extends BufferedBagOStuffTest {
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

		$this->bufferedCache = new LocalBufferedBagOStuff( $this->cache );
		$this->bufferedCache->begin();
	}
}
