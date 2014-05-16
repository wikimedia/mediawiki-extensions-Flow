<?php

namespace Flow\Tests;

use Flow\Model\UUID;

/**
 * @group Flow
 */
class UUIDTest extends FlowTestCase {

	static public function uuidConversionProvider() {
		// sample uuid from UIDGenerator::newTimestampedUID128()
		$numeric_128 = '6709199728898751234959525538795913762';
		$hex_128 = wfBaseConvert( $numeric_128, 10, 16, 32 );
		$bin_128 = pack( 'H*', $hex_128 );
		$pretty_128 = wfBaseConvert( $numeric_128, 10, 36 );

		// Conversion from 128 bit to 88 bit takes the left
		// most 88 bits.
		$bits_88 = substr( wfBaseConvert( $numeric_128, 10, 2, 128 ), 0, 88 );
		$numeric_88 = wfBaseConvert( $bits_88, 2, 10 );
		$hex_88 = wfBaseConvert( $numeric_88, 10, 16, 22 );
		$bin_88 = pack( 'H*', $hex_88 );
		$pretty_88 = wfBaseConvert( $numeric_88, 10, 36 );

		return array(
			array(
				'128 bit hex input must be truncated to 88bit output',
				// input
				$hex_128,
				// binary
				$bin_88,
				// hex
				$hex_88,
				// base36 output
				$pretty_88,
			),

			array(
				'88 bit binary input',
				// input
				$bin_88,
				// binary
				$bin_88,
				// hex
				$hex_88,
				// pretty
				$pretty_88,
			),

			array(
				'88 bit numeric input',
				// input
				$numeric_88,
				// binary
				$bin_88,
				// hex
				$hex_88,
				// pretty
				$pretty_88,
			),

			array(
				'88 bit hex input',
				// input
				$hex_88,
				// binary
				$bin_88,
				// hex
				$hex_88,
				// pretty
				$pretty_88,
			),

			array(
				'88 bit pretty input',
				// input
				$pretty_88,
				// binary
				$bin_88,
				// hex
				$hex_88,
				// pretty
				$pretty_88,
			),

		);
	}

	/**
	 * @dataProvider uuidConversionProvider
	 */
	public function testUUIDConversion( $msg, $input, $binary, $hex, $pretty ) {
		$uuid = UUID::create( $input );

		$this->assertEquals( $binary, $uuid->getBinary(), "Compare binary: $msg" );
		//$this->assertEquals( $hex, $uuid->getHex(), "Compare hex: $msg" );
		$this->assertEquals( $pretty, $uuid->getAlphadecimal(), "Compare pretty: $msg" );
	}

	static public function prettyProvider() {
		return array(
			// maximal base 36 value ( 2^88 )
			array( '12vwzoefjlykjgcnwf' ),
			// current unpadded values from uidgenerator
			array( 'rlnn1941hqtdtn8a' ),
		);
	}

	/**
	 * @dataProvider prettyProvider
	 */
	public function testUnpaddedPrettyUuid( $uuid ) {
		$this->assertEquals( $uuid, UUID::create( $uuid )->getAlphadecimal() );
	}
}
