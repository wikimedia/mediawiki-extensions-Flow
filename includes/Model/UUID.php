<?php

namespace Flow\Model;

use Flow\Data\ObjectManager;
use MWException;

class UUID {
	// provided binary UUID
	protected $binaryValue;
	// alternate representations
	protected $hexValue;
	protected $prettyValue;

	protected $timestamp;

	// UUID length in hex, always padded
	const HEX_LEN = 22;
	// UUID length in binary, always padded
	const BIN_LEN = 11;
	// UUID length in base36, with padding
	const PRETTY_LEN = 19;
	// unpadded base36 input string
	const MIN_PRETTY_LEN = 16;

	// 126 bit binary length
	const OLD_BIN_LEN = 16;
	// 128 bit hex length
	const OLD_HEX_LEN = 32;

	function __construct( $binaryValue ) {
		if ( strlen( $binaryValue ) !== self::BIN_LEN ) {
			throw new \InvalidArgumentException( 'Expected ' . self::BIN_LEN . ' char binary string, got: ' . $binaryValue );
		}
		$this->binaryValue = $binaryValue;
	}

	static public function create( $input = false ) {
		$binaryValue = null;
		$hexValue = null;
		$prettyValue = null;

		if ( is_object( $input ) ) {
			if ( $input instanceof UUID ) {
				return clone $input;
			} else {
				throw new MWException( "Got unknown input of type " . get_class( $input ) );
			}
		} elseif ( $input === null ) {
			return null;
		}

		$len = strlen( $input );
		if ( $input === false ) {
			// new uuid in base 16 and pad to HEX_LEN with 0's
			$hexValue = str_pad( \UIDGenerator::newTimestampedUID88( 16 ), self::HEX_LEN, '0', STR_PAD_LEFT );
		} elseif ( !is_string( $input ) && !is_int( $input ) ) {
			throw new \MWException( "Unknown input type to UUID class: " . gettype( $input ) );
		} elseif ( $len === self::BIN_LEN ) {
			$binaryValue = $input;
		} elseif ( $len >= self::MIN_PRETTY_LEN && $len <= self::PRETTY_LEN && ctype_alnum( $input ) ) {
			$prettyValue = $input;
			// convert base 36 to base 16 and pad to HEX_LEN with 0's
			$hexValue = wfBaseConvert( $input, 36, 16, self::HEX_LEN );
		} elseif ( $len === self::HEX_LEN && preg_match( '/^[a-fA-F0-9]+$/', $input ) ) {
			$hexValue = $input;
		} elseif ( $len === self::OLD_BIN_LEN ) {
			$binaryValue = substr( $input, 0, self::BIN_LEN );
		} elseif ( $len === self::OLD_HEX_LEN ) {
			$hexValue = substr( $input, 0, self::HEX_LEN );
		} elseif ( is_numeric( $input ) ) {
			// convert base 10 to base 16 and pad to HEX_LEN with 0's
			$hexValue = wfBaseConvert( $input, 10, 16, self::HEX_LEN );
		} else {
			$len = $len;
			$type = gettype( $input );
			throw new \MWException( "Unknown input to UUID class : $type : $len" );
		}

		if ( $binaryValue === null && $hexValue !== null ) {
			$binaryValue = pack( 'H*', $hexValue );
		}
		$uuid = new self( $binaryValue );
		$uuid->hexValue = $hexValue;
		$uuid->prettyValue = $prettyValue;

		return $uuid;
	}

	public function __toString() {
		return $this->getPretty();
	}

	public function getHex() {
		if ( $this->hexValue === null ) {
			$this->hexValue = str_pad( bin2hex( $this->binaryValue ), self::HEX_LEN, '0', STR_PAD_LEFT );
		}
		return $this->hexValue;
	}

	public function getBinary() {
		return $this->binaryValue;
	}

	public function getNumber() {
		return wfBaseConvert( $this->getHex(), 16, 10 );
	}

	public function getTimestampObj() {
		if ( $this->timestamp === null ) {
			// First 6 bytes === 48 bits
			$hex = $this->getHex();
			$timePortion = substr( $hex, 0, 12 );
			$bits_48 = wfBaseConvert( $timePortion, 16, 2, 48 );
			$bits_46 = substr( $bits_48, 0, 46 );
			$msTimestamp = wfBaseConvert( $bits_46, 2, 10 );

			try {
				$this->timestamp = new \MWTimestamp( intval( $msTimestamp / 1000 ) );
			} catch ( \TimestampException $e ) {
				wfDebugLog( __CLASS__, __FUNCTION__ . ": bogus time value: UUID=$hex; VALUE=$msTimestamp" );
				return false;
			}
		}
		return clone $this->timestamp;
	}

	public function getTimestamp() {
		$ts = $this->getTimestampObj();
		return $ts ? $ts->getTimestamp( TS_MW ) : false;
	}

	public function getHumanTimestamp( $relativeTo = null, User $user = null, Language $lang = null ) {
		if ( $relativeTo instanceof UUID ) {
			$relativeTo = $relativeTo->getTimestampObj() ?: null;
		}
		$ts = $this->getTimestampObj();
		return $ts ? $ts->getHumanTimestamp( $relativeTo, $user, $lang ) : false;
	}

	public static function convertUUIDs( $array ) {
		foreach( ObjectManager::makeArray( $array ) as $key => $value ) {
			if ( is_a( $value, 'Flow\Model\UUID' ) ) {
				$array[$key] = $value->getBinary();
			}
		}

		return $array;
	}

	public function equals( UUID $other ) {
		return $other->getBinary() === $this->getBinary();
	}

	/**
	 * Generates a fake UUID for a given timestamp that will have comparison
	 * results equivalent to a real UUID generated at that time
	 * @param  mixed $ts Something accepted by wfTimestamp()
	 * @return UUID object.
	 */
	public static function getComparisonUUID( $ts ) {
		// It should be comparable with UUIDs in binary mode.
		// Easiest way to do this is to take the 46 MSBs of the UNIX timestamp * 1000
		// and pad the remaining characters with zeroes.
		$millitime = wfTimestamp( TS_UNIX, $ts ) * 1000;
		$timestampBinary = wfBaseConvert( $millitime, 10, 2, 46 );
		$uuidBase2 = str_pad( $timestampBinary, 16 * 8, '0', STR_PAD_RIGHT );
		$uuidHex = wfBaseConvert( $uuidBase2, 2, 16, 32 );

		return self::create( $uuidHex );
	}

	/**
	 * pretty-ish ;)
	 */
	public function getPretty() {
		if ( $this->prettyValue === null ) {
			$this->prettyValue = wfBaseConvert( $this->getHex(), 16, 36 );
		}
		return $this->prettyValue;
	}
}
