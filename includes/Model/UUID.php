<?php

namespace Flow\Model;

use Flow\Data\ObjectManager;
use Flow\Exception\InvalidInputException;
use User;
use Language;
use MWTimestamp;

/**
 * Immutable class modeling timestamped UUID's from
 * the core UIDGenerator.
 *
 * @todo probably should be UID since these dont match the UUID standard
 */
class UUID {
	/**
	 * @var UUID[][][]
	 */
	private static $instances;

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

	/**
	 * Acceptable input values for constructor.
	 * Values are the property names the input data will be saved to.
	 *
	 * @var string
	 */
	const INPUT_BIN = 'binaryValue',
		INPUT_HEX = 'hexValue',
		INPUT_ALNUM = 'alphadecimalValue';

	// UUID length in hex, always padded
	const HEX_LEN = 22;
	// UUID length in binary, always padded
	const BIN_LEN = 11;
	// UUID length in base36, with padding
	const ALNUM_LEN = 19;
	// unpadded base36 input string
	const MIN_ALNUM_LEN = 16;

	// 126 bit binary length
	const OLD_BIN_LEN = 16;
	// 128 bit hex length
	const OLD_HEX_LEN = 32;

	/**
	 * Constructs a UUID object based on either the binary, hex or alphanumeric
	 * representation.
	 *
	 * @param string $value UUID value
	 * @param string $format UUID format (static::INPUT_BIN, static::input_HEX
	 *  or static::input_ALNUM)
	 * @throws InvalidInputException
	 */
	protected function __construct( $value, $format ) {
		if ( !in_array( $format, array( static::INPUT_BIN, static::INPUT_HEX, static::INPUT_ALNUM ) ) ) {
			throw new InvalidInputException( 'Invalid UUID input format: ' . $format, 'invalid-input' );
		}

		// doublecheck validity of inputs, based on pre-determined lengths
		$len = strlen( $value );
		if ( $format === static::INPUT_BIN && $len !== self::BIN_LEN ) {
			throw new InvalidInputException( 'Expected ' . self::BIN_LEN . ' char binary string, got: ' . $value, 'invalid-input' );
		} elseif ( $format === static::INPUT_HEX && $len !== self::HEX_LEN ) {
			throw new InvalidInputException( 'Expected ' . self::HEX_LEN . ' char hex string, got: ' . $value, 'invalid-input' );
		} elseif ( $format === static::INPUT_ALNUM && ( $len < self::MIN_ALNUM_LEN || $len > self::ALNUM_LEN || !ctype_alnum( $value ) ) ) {
			throw new InvalidInputException( 'Expected ' . self::MIN_ALNUM_LEN . ' to ' . self::ALNUM_LEN . ' char alphanumeric string, got: ' . $value, 'invalid-input' );
		}

		self::$instances[$format][$value] = $this;
		$this->{$format} = $value;
	}

	/**
	 * Alphanumeric value is all we need to construct a UUID object; saving
	 * anything more is just wasted storage/bandwidth.
	 *
	 * @return array
	 */
	public function __sleep() {
		// ensure alphadecimal is populated
		$this->getAlphadecimal();
		return array( 'alphadecimalValue' );
	}

	public function __wakeup() {
		if ( $this->binaryValue ) {
			// some B/C code
			$this->binaryValue = substr( $this->binaryValue, 0, self::BIN_LEN );
		}
	}

	/**
	 * Returns a UUID objects based on given input. Will automatically try to
	 * determine the input format of the given $input or fail with an exception.
	 *
	 * @param mixed $input
	 * @return UUID|null
	 * @throws InvalidInputException
	 */
	static public function create( $input = false ) {
		// Most calls to UUID::create are binary strings, check string first
		if ( is_string( $input ) || is_int( $input) || $input === false ) {
			if ( $input === false ) {
				// new uuid in base 16 and pad to HEX_LEN with 0's
				$hexValue = str_pad( \UIDGenerator::newTimestampedUID88( 16 ), self::HEX_LEN, '0', STR_PAD_LEFT );
				return new static( $hexValue, static::INPUT_HEX );
			} else {
				$len = strlen( $input );
				if ( $len === self::BIN_LEN ) {
					$value = $input;
					$type = static::INPUT_BIN;
				} elseif ( $len >= self::MIN_ALNUM_LEN && $len <= self::ALNUM_LEN && ctype_alnum( $input ) ) {
					$value = $input;
					$type = static::INPUT_ALNUM;
				} elseif ( $len === self::HEX_LEN && preg_match( '/^[a-fA-F0-9]+$/', $input ) ) {
					$value = $input;
					$type = static::INPUT_HEX;
				} elseif ( $len === self::OLD_BIN_LEN ) {
					$value = substr( $input, 0, self::BIN_LEN );
					$type = static::INPUT_BIN;
				} elseif ( $len === self::OLD_HEX_LEN ) {
					$value = substr( $input, 0, self::HEX_LEN );
					$type = static::INPUT_HEX;
				} elseif ( is_numeric( $input ) ) {
					// convert base 10 to base 16 and pad to HEX_LEN with 0's
					$value = wfBaseConvert( $input, 10, 16, self::HEX_LEN );
					$type = static::INPUT_HEX;
				} else {
					throw new InvalidInputException( 'Unknown input to UUID class', 'invalid-input' );
				}

				if ( isset( self::$instances[$type][$value] ) ) {
					return self::$instances[$type][$value];
				} else {
					return new static( $value, $type );
				}
			}
		} else if ( is_object( $input ) ) {
			if ( $input instanceof UUID ) {
				return $input;
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
		wfWarn( __METHOD__ . ': UUID __toString auto-converted to alphaDecimal; please do manually.' );

		return $this->getAlphadecimal();
	}

	/**
	 * @return string
	 */
	public function getBinary() {
		if ( $this->binaryValue !== null ) {
			return $this->binaryValue;
		} elseif ( $this->hexValue !== null ) {
			$this->binaryValue = static::hex2bin( $this->hexValue );
		} elseif ( $this->alphadecimalValue !== null ) {
			$this->hexValue = static::alnum2hex( $this->alphadecimalValue );
			self::$instances[self::INPUT_HEX][$this->hexValue] = $this;
			$this->binaryValue = static::hex2bin( $this->hexValue );
		}
		self::$instances[self::INPUT_BIN][$this->binaryValue] = $this;
		return $this->binaryValue;
	}

	/**
	 * @return string
	 */
	protected function getHex() {
		if ( $this->hexValue !== null ) {
			return $this->hexValue;
		} elseif ( $this->binaryValue !== null ) {
			$this->hexValue = static::bin2hex( $this->binaryValue );
		} elseif ( $this->alphadecimalValue !== null ) {
			$this->hexValue = static::alnum2hex( $this->alphadecimalValue );
		}
		self::$instances[self::INPUT_HEX][$this->hexValue] = $this;
		return $this->hexValue;
	}

	/**
	 * @return string base 36 representation
	 */
	public function getAlphadecimal() {
		if ( $this->alphadecimalValue !== null ) {
			return $this->alphadecimalValue;
		} elseif ( $this->hexValue !== null ) {
			$this->alphadecimalValue = static::hex2alnum( $this->hexValue );
		} elseif ( $this->binaryValue !== null ) {
			$this->hexValue = static::bin2hex( $this->binaryValue );
			self::$instances[self::INPUT_HEX][$this->hexValue] = $this;
			$this->alphadecimalValue = static::hex2alnum( $this->hexValue );
		}
		self::$instances[self::INPUT_ALNUM][$this->alphadecimalValue] = $this;
		return $this->alphadecimalValue;
	}

	/**
	 * @return MWTimestamp
	 * @throws \TimestampException
	 */
	public function getTimestampObj() {
		if ( $this->timestamp === null ) {
			// First 6 bytes === 48 bits
			$hex = $this->getHex();
			$timePortion = substr( $hex, 0, 12 );
			$bits_48 = wfBaseConvert( $timePortion, 16, 2, 48 );
			$bits_46 = substr( $bits_48, 0, 46 );
			$msTimestamp = wfBaseConvert( $bits_46, 2, 10 );

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
	public static function convertUUIDs( $array, $format = 'binary' ) {
		foreach( ObjectManager::makeArray( $array ) as $key => $value ) {
			if ( $value instanceof UUID ) {
				if ( $format === 'binary' ) {
					$array[$key] = $value->getBinary();
				} elseif ( $format === 'alphadecimal' ) {
					$array[$key] = $value->getAlphadecimal();
				}
			} elseif ( is_string( $value ) && substr( $key, -3 ) === '_id' ) {
				$len = strlen( $value );
				if ( $format === 'alphadecimal' && $len === self::BIN_LEN ) {
					$array[$key] = UUID::create( $value )->getAlphadecimal();
				} elseif ( $format === 'binary' && (
					( $len >= self::MIN_ALNUM_LEN && $len <= self::ALNUM_LEN )
					||
					$len === self::HEX_LEN
				) ) {
					$array[$key] = UUID::create( $value )->getBinary();
				}
			}
		}

		return $array;
	}

	/**
	 * @param UUID $other
	 * @return boolean
	 */
	public function equals( UUID $other ) {
		return $other->getAlphadecimal() === $this->getAlphadecimal();
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
	 * Converts binary UUID to HEX.
	 *
	 * @param string $binary Binary string (not a string of 1s & 0s)
	 * @return string
	 */
	public static function bin2hex( $binary ) {
		return str_pad( bin2hex( $binary ), self::HEX_LEN, '0', STR_PAD_LEFT );
	}

	/**
	 * Converts alphanumeric UUID to HEX.
	 *
	 * @param string $alnum
	 * @return string
	 */
	public static function alnum2hex( $alnum ) {
		return str_pad( wfBaseConvert( $alnum, 36, 16 ), self::HEX_LEN, '0', STR_PAD_LEFT );
	}

	/**
	 * Convert HEX UUID to binary string.
	 *
	 * @param string $hex
	 * @return string Binary string (not a string of 1s & 0s)
	 */
	public static function hex2bin( $hex ) {
		return pack( 'H*', $hex );
	}

	/**
	 * Converts HEX UUID to alphanumeric.
	 *
	 * @param string $hex
	 * @return string
	 */
	public static function hex2alnum( $hex ) {
		return wfBaseConvert( $hex, 16, 36 );
	}
}
