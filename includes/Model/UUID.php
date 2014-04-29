<?php

namespace Flow\Model;

use Flow\Data\ObjectManager;
use Flow\Exception\InvalidInputException;
use User;
use Language;
use MapCacheLRU;
use MWTimestamp;

/**
 * Immutable class modeling timestamped UUID's from
 * the core UIDGenerator.
 *
 * @todo probably should be UID since these dont match the UUID standard
 */
class UUID {
	/**
	 * UUID::create maintains a cache to avoid expensive conversions between
	 * binary and alphadecimal. On a batch operation this can become a memory
	 * leak if not bounded. After hitting this many UUID's prune the cache
	 * with an LRU algo.
	 */
	const CACHE_MAX = 1000;

	/**
	 * @var MapCacheLRU Maps from binary uuid string to UUID object
	 */
	protected static $cache;

	/**
	 * Provided binary UUID
	 *
	 * @var string
	 */
	protected $binaryValue;

	/**
	 * base16 representation
	 *
	 * @var string
	 */
	protected $hexValue;

	/**
	 * base36 representation
	 *
	 * @var string
	 */
	protected $alphadecimalValue;

	/**
	 * Timestamp uuid was created
	 *
	 * @var MWTimestamp
	 */
	protected $timestamp;

	// UUID length in hex, always padded
	const HEX_LEN = 22;
	// UUID length in binary, always padded
	const BIN_LEN = 11;
	// UUID length in base36, with padding
	const ALPHADECIMAL_LEN = 19;
	// unpadded base36 input string
	const MIN_ALPHADECIMAL_LEN = 16;

	// 126 bit binary length
	const OLD_BIN_LEN = 16;
	// 128 bit hex length
	const OLD_HEX_LEN = 32;

	/**
	 * @param string $binaryValue
	 * @throws InvalidInputException
	 */
	function __construct( $binaryValue ) {
		if ( strlen( $binaryValue ) !== self::BIN_LEN ) {
			throw new InvalidInputException( 'Expected ' . self::BIN_LEN . ' char binary string, got: ' . $binaryValue, 'invalid-input' );
		}
		$this->binaryValue = $binaryValue;
	}

	/**
	 * Binary value is all we need to construct a UUID object; saving anything
	 * more is just wasted storage/bandwidth.
	 *
	 * @return array
	 */
	public function __sleep() {
		return array( 'binaryValue' );
	}

	public function __wakeup() {
		if ( strlen( $this->binaryValue ) === self::BIN_LEN ) {
			return;
		}
		$this->binaryValue = substr( $this->binaryValue, 0, self::BIN_LEN );
		$this->hexValue = null;
		$this->alphadecimalValue = null;
	}

	/**
	 * @param mixed $input
	 * @return UUID|null
	 * @throws InvalidInputException
	 */
	static public function create( $input = false ) {
		// Most calls to UUID::create are binary strings, check string first
		if ( is_string( $input ) || is_int( $input) || $input === false ) {
			$binaryValue = null;
			$hexValue = null;
			$alphadecimalValue = null;

			if ( $input === false ) {
				// new uuid in base 16 and pad to HEX_LEN with 0's
				$hexValue = str_pad( \UIDGenerator::newTimestampedUID88( 16 ), self::HEX_LEN, '0', STR_PAD_LEFT );
			} else {
				$len = strlen( $input );
				if ( $len === self::BIN_LEN ) {
					$binaryValue = $input;
				} elseif ( $len >= self::MIN_ALPHADECIMAL_LEN && $len <= self::ALPHADECIMAL_LEN && ctype_alnum( $input ) ) {
					$alphadecimalValue = $input;
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
					throw new InvalidInputException( 'Unknown input to UUID class', 'invalid-input' );
				}
			}

			if ( $binaryValue === null && $hexValue !== null ) {
				$binaryValue = pack( 'H*', $hexValue );
			}

			// uuid's are immutable
			if ( self::$cache === null ) {
				self::$cache = new MapCacheLRU( self::CACHE_MAX );
			}
			$uuid = self::$cache->get( $binaryValue );
			if ( $uuid === null ) {
				$uuid = new self( $binaryValue );
				$uuid->hexValue = $hexValue;
				$uuid->alphadecimalValue = $alphadecimalValue;
				self::$cache->set( $binaryValue, $uuid );
			}
			return $uuid;
		} else if ( is_object( $input ) ) {
			if ( $input instanceof UUID ) {
				return clone $input;
			} else {
				throw new InvalidInputException( 'Unknown input of type ' . get_class( $input ), 'invalid-input' );
			}
		} elseif ( $input === null ) {
			return null;
		} else {
			throw new InvalidInputException( 'Unknown input type to UUID class: ' . gettype( $input ), 'invalid-input' );
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		echo '<pre>', new \Exception;
		die();

		wfWarn( __METHOD__ . ': UUID __toString auto-converted to alphaDecimal; please do manually.' );

		return $this->getAlphadecimal();
	}

	/**
	 * @return string
	 */
	protected function getHex() {
		if ( $this->hexValue === null ) {
			$this->hexValue = str_pad( bin2hex( $this->binaryValue ), self::HEX_LEN, '0', STR_PAD_LEFT );
		}
		return $this->hexValue;
	}

	/**
	 * @return string
	 */
	public function getBinary() {
		return $this->binaryValue;
	}

	/**
	 * @return MWTimestamp
	 * @throws \TimestampException
	 */
	public function getTimestampObj() {
		if ( $this->timestamp === null ) {
			if ( $this->alphadecimalValue ) {
				$bits = wfBaseConvert( $this->alphadecimalValue, 36, 2, 88 );
			} else {
				// First 6 bytes === 48 bits
				$bits = wfBaseConvert( $this->getHex(), 16, 2, 88 );
			}
			$msTimestamp = wfBaseConvert( substr( $bits, 0, 46 ), 2, 10 );

			try {
				$this->timestamp = new MWTimestamp( intval( $msTimestamp / 1000 ) );
			} catch ( \TimestampException $e ) {
				wfDebugLog( 'Flow', __METHOD__ . ": bogus time value: UUID=$hex; VALUE=$msTimestamp" );
				throw $e;
			}
		}
		return clone $this->timestamp;
	}

	/**
	 * @return string Timestamp in TS_MW format
	 */
	public function getTimestamp() {
		$ts = $this->getTimestampObj();
		return $ts->getTimestamp( TS_MW );
	}

	/**
	 * @param UUID|MWTimestamp|null $relativeTo
	 * @param User|null $user
	 * @param Language|null $lang
	 * @return string|false
	 * @throws InvalidInputException
	 */
	public function getHumanTimestamp( $relativeTo = null, User $user = null, Language $lang = null ) {
		if ( $relativeTo instanceof UUID ) {
			$rel = $relativeTo->getTimestampObj();
		} elseif ( $relativeTo instanceof MWTimestamp ) {
			$rel = $relativeTo;
		} else {
			throw new InvalidInputException( 'Expected MWTimestamp or UUID, got ' . get_class( $relativeTo ), 'invalid-input' );
		}
		$ts = $this->getTimestampObj();
		return $ts ? $ts->getHumanTimestamp( $rel, $user, $lang ) : false;
	}

	/**
	 * @param array
	 * @return array
	 */
	public static function convertUUIDs( $array ) {
		$array = ObjectManager::makeArray( $array );
		foreach( $array as $key => $value ) {
			if ( $value instanceof UUID ) {
				$array[$key] = $value->getBinary();
			}
		}

		return $array;
	}

	/**
	 * @param UUID $other
	 * @return boolean
	 */
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
		// base 10 -> base 2, taking 46 bits
		$timestampBinary = wfBaseConvert( $millitime, 10, 2, 46 );
		// pad out the 46 bits to binary len with 0's
		$uuidBase2 = str_pad( $timestampBinary, self::BIN_LEN * 8, '0', STR_PAD_RIGHT );
		// base 2 -> base 16
		$uuidHex = wfBaseConvert( $uuidBase2, 2, 16, self::HEX_LEN );

		return self::create( $uuidHex );
	}

	/**
	 * @return string base 36 representation
	 */
	public function getAlphadecimal() {
		if ( $this->alphadecimalValue === null ) {
			$this->alphadecimalValue = wfBaseConvert( $this->getHex(), 16, 36 );
		}
		return $this->alphadecimalValue;
	}
}
