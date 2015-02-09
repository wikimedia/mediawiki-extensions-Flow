<?php

namespace Flow\Tests;

use BagOStuff;
use EmptyBagOStuff;
use Flow\Data\BagOStuff\BufferedBagOStuff;
use HashBagOStuff;
use MediaWikiTestCase;
use MultiWriteBagOStuff;
use ObjectCache;

/**
 * @group Flow
 */
class BufferedBagOStuffTest extends MediaWikiTestCase {
	/**
	 * @var BagOStuff
	 */
	protected $cache;

	/**
	 * @var BufferedBagOStuff
	 */
	protected $bufferedCache;

	/**
	 * Array of keys used in these tests, so we can clear them on tearDown.
	 *
	 * @var string[]
	 */
	protected $keys = array( 'key', 'key2' );

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

		$this->bufferedCache = new BufferedBagOStuff( $this->cache );
		$this->bufferedCache->begin();
	}

	protected function tearDown() {
		// make sure all keys written to in any of these tests are deleted from
		// the real cache
		foreach ( $this->keys as $key ) {
			$this->cache->delete( $key );
		}

		parent::tearDown();
	}

	public function testGetAndSet() {
		$this->bufferedCache->set( 'key', 'value' );

		// check that the value is only set on bufferedCache, not yet on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( false, $this->cache->get( 'key' ) );

		$this->bufferedCache->commit();

		// check that the value is also set on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value', $this->cache->get( 'key' ) );
	}

	public function testAdd() {
		$this->bufferedCache->add( 'key', 'value' );

		// check that the value is only set on bufferedCache, not yet on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( false, $this->cache->get( 'key' ) );

		$this->bufferedCache->commit();

		// check that the value is also set on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value', $this->cache->get( 'key' ) );
	}

	public function testAddFailImmediately() {
		$this->cache->set( 'key', 'value' );
		$this->bufferedCache->add( 'key', 'value-2' );

		$this->bufferedCache->commit();

		// check that the value is not added on bufferedCache, nor on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value', $this->cache->get( 'key' ) );
	}

	public function testAddFailDeferred() {
		$this->bufferedCache->add( 'key', 'value' );

		// something else directly sets the key in the meantime...
		$this->cache->set( 'key', 'value-2' );

		// check that the value has been added to buffered cache but not yet to real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value-2', $this->cache->get( 'key' ) );

		$this->bufferedCache->commit();

		// check that the value failed to add and the key was properly cleared
		$this->assertEquals( false, $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( false, $this->cache->get( 'key' ) );
	}

	public function testDelete() {
		$this->cache->set( 'key', 'value' );

		$this->bufferedCache->delete( 'key' );

		// check that the value has been deleted from bufferedcache (only)
		$this->assertEquals( false, $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value', $this->cache->get( 'key' ) );

		$this->bufferedCache->commit();

		// check that the value has also been deleted from real cache
		$this->assertEquals( false, $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( false, $this->cache->get( 'key' ) );
	}

	public function testGetMulti() {
		$localValues = array(
			'key' => 'value',
		);
		$cacheValues = array(
			'key2' => 'value2',
		);

		foreach ( $localValues as $key => $value ) {
			$this->bufferedCache->set( $key, $value );
		}

		foreach ( $cacheValues as $key => $value ) {
			$this->cache->set( $key, $value );
		}

		// check that we're able to read the values from both buffered & real cache
		$this->assertEquals( $localValues + $cacheValues, $this->bufferedCache->getMulti( array_keys( $localValues + $cacheValues ) ) );

		// tearDown will cleanup everything that's been stored via buffered cache,
		// however, this one went directly to real cache - clean up!
		$this->cache->delete( 'key2' );
	}

	public function testSetMulti() {
		$this->bufferedCache->setMulti( array(
			'key' => 'value',
			'key2' => 'value2',
		) );

		// check that the values are only set on bufferedCache, not yet on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value2', $this->bufferedCache->get( 'key2' ) );
		$this->assertEquals( false, $this->cache->get( 'key' ) );
		$this->assertEquals( false, $this->cache->get( 'key2' ) );

		$this->bufferedCache->commit();

		// check that the values are also set on real cache
		$this->assertEquals( 'value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'value2', $this->bufferedCache->get( 'key2' ) );
		$this->assertEquals( 'value', $this->cache->get( 'key' ) );
		$this->assertEquals( 'value2', $this->cache->get( 'key2' ) );
	}

	public function testMerge() {
		$this->cache->set( 'key', 'value' );

		$callback = function( \BagOStuff $cache, $key, $value ) {
			return 'merged-value';
		};
		$this->bufferedCache->merge( 'key', $callback );

		$this->bufferedCache->commit();

		// check that the values are merged both in buffered & real cache
		$this->assertEquals( 'merged-value', $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( 'merged-value', $this->cache->get( 'key' ) );
	}

	// Can't make a test for merge to fail immediately: the buffered part is
	// only in memory, for the current process. There is no way something else
	// will be able to overwrite something in there.

	public function testMergeFailDelayed() {
		/*
		 * Test concurrent merges by forking this process, if:
		 * - not manually called with --use-bagostuff
		 * - pcntl_fork is supported by the system
		 * - cache type will correctly support calls over forks
		 */
		$fork = (bool) $this->getCliArg( 'use-bagostuff' );
		$fork &= function_exists( 'pcntl_fork' );
		$fork &= !$this->cache instanceof HashBagOStuff;
		$fork &= !$this->cache instanceof EmptyBagOStuff;
		$fork &= !$this->cache instanceof MultiWriteBagOStuff;

		if ( !$fork ) {
			$this->markTestSkipped( "Unable to fork, can't test merge" );
		}

		$this->cache->set( 'key', 'value' );

		$callback = function ( \BagOStuff $cache, $key, $value ) {
			// prepend merged to whatever is in cache
			return 'merged-' . (string) $value;
		};
		$this->bufferedCache->merge( 'key', $callback, 0, 1 );

		// callback should take awhile now so that we can test concurrent merge attempts
		$pid = pcntl_fork();
		if ( $pid == -1 ) {
			// can't fork, ignore this test...
		} elseif ( $pid ) {
			// wait a little, making sure that the child process is calling merge
			usleep( 3000 );

			// attempt a merge - this should fail to persist to real cache
			$this->bufferedCache->commit();

			// make sure the child's merge is completed and verify
			usleep( 3000 );

			// check that the values failed to merge
			$this->assertEquals( false, $this->bufferedCache->get( 'key' ) );
			$this->assertEquals( false, $this->cache->get( 'key' ) );
		} else {
			$this->bufferedCache->commit();

			// before exiting, tear down - MediaWikiTestCase will check for it
			// on destruct & throw an exception if it wasn't called
			$this->tearDown();

			// Note: I'm not even going to check if the merge worked, I'll
			// compare values in the parent process to test if this merge worked.
			// I'm just going to exit this child process, since I don't want the
			// child to output any test results (would be rather confusing to
			// have test output twice)
			exit;
		}
	}

	public function testRollback() {
		$this->cache->set( 'key', 'value' );

		$this->bufferedCache->set( 'key', 'value-2' );
		$this->bufferedCache->add( 'key2', 'value-2' );

		// something else directly sets the key in the meantime...
		$this->cache->set( 'key2', 'value' );

		$this->bufferedCache->commit();

		// both changes should have been "rolled back" and both keys should've
		// been cleared, in both buffered & real cache
		$this->assertEquals( false, $this->bufferedCache->get( 'key' ) );
		$this->assertEquals( false, $this->bufferedCache->get( 'key2' ) );
		$this->assertEquals( false, $this->cache->get( 'key' ) );
		$this->assertEquals( false, $this->cache->get( 'key2' ) );
	}
}
