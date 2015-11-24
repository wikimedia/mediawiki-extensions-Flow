<?php

use Flow\Model\UUID;

require_once __DIR__ . '/../../../maintenance/benchmarks/Benchmarker.php';

/**
 * @ingroup Benchmark
 */
class BenchUuidConversions extends \Benchmarker {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Benchmark uuid timstamp extraction implementations';
	}

	public function execute() {
		// sample values slightly different to avoid
		// UUID internal caching
		$alpha = UUID::hex2alnum( '0523f95ac547ab45266762' );
		$binary = UUID::hex2bin( '0523f95af547ab45266762' );
		$hex = '0423f95af547ab45266762';

		// benchmarker requires we pass an object
		$id = UUID::create();

		$this->bench( array(
			array(
				'function' => array( $id, 'bin2hex' ),
				'args' => array( $binary ),
			),
			array(
				'function' => array( $id, 'alnum2hex' ),
				'args' => array( $alpha ),
			),
			array(
				'function' => array( $id, 'hex2bin' ),
				'args' => array( $hex ),
			),
			array(
				'function' => array( $id, 'hex2alnum' ),
				'args' => array( $hex ),
			),
			array(
				'function' => array( $id, 'hex2timestamp' ),
				'args' => array( $hex ),
			),
			array(
				'function' => array( $this, 'oldhex2timestamp' ),
				'args' => array( $hex ),
			),
			array(
				'function' => array( $this, 'oldalphadecimal2timestamp' ),
				'args' => array( $alpha ),
			),
			array(
				'function' => array( $this, 'case1' ),
				'args' => array( $binary ),
			),
			array(
				'function' => array( $this, 'case2' ),
				'args' => array( $binary ),
			),
			array(
				'function' => array( $this, 'case3' ),
				'args' => array( $alpha ),
			),
			array(
				'function' => array( $this, 'case4' ),
				'args' => array( $alpha ),
			),
		) );

		$this->output( $this->getFormattedResults() );
	}

	public function oldhex2timestamp( $hex ) {
		$bits = \Wikimedia\base_convert( $hex, 16, 2, 88 );
		$msTimestamp = \Wikimedia\base_convert( substr( $bits, 0, 46 ), 2, 10 );
		return intval( $msTimestamp / 1000 );
	}

	public function oldalphadecimal2timestamp( $alpha ) {
		$bits = \Wikimedia\base_convert( $alpha, 36, 2, 88 );
		$msTimestamp = \Wikimedia\base_convert( substr( $bits, 0, 46 ), 2, 10 );
		return intval( $msTimestamp / 1000 );
	}

	/**
	 * Common case 1: binary from database to alpha and timestamp.
	 */
	public function case1( $binary ) {
		// clone to avoid internal object caching
		$id = clone UUID::create( $binary );
		$id->getAlphadecimal();
		$id->getTimestampObj();
	}

	/**
	 * Common case 2: binary from database to timestamp and alpha.
	 * Probably same as case 1, but who knows.
	 */
	public function case2( $binary ) {
		// clone to avoid internal object caching
		$id = clone UUID::create( $binary );
		$id->getTimestampObj();
		$id->getAlphadecimal();
	}

	/**
	 * Common case 3: alphadecimal from cache to timestamp and binary.
	 */
	public function case3( $alpha ) {
		// clone to avoid internal object caching
		$id = clone UUID::create( $alpha );
		$id->getBinary();
		$id->getTimestampObj();
	}

	/**
	 * Common case 4: alphadecimal from cache to bianry and timestamp.
	 */
	public function case4( $alpha ) {
		// clone to avoid internal object caching
		$id = clone UUID::create( $alpha );
		$id->getTimestampObj();
		$id->getBinary();
	}
}

$maintClass = 'BenchUuidConversions';
require_once RUN_MAINTENANCE_IF_MAIN;
