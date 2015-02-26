<?php

namespace Flow\Model;

use ApiSerializable;
use Blob;
use Flow\Data\ObjectManager;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidInputException;
use Language;
use MWTimestamp;
use TimestampException;
use User;

/**
 * Immutable class modeling timestamped UUID's from
 * the core UIDGenerator.
 *
 * @todo probably should be UID since these dont match the UUID standard
 */
class UUID implements ApiSerializable {
	/**
	 * @var UUID[][][]
	 */
	private static $instances;

	/**
	 * binary UUID string
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
	 * @var MWTimestamp|null
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

		// If this is not a binary UUID, reject any string containing upper case characters.
		if ( $format !== self::INPUT_BIN && $value !== strtolower( $value ) ) {
			throw new InvalidInputException( 'Input UUID strings must be lowercase', 'invalid-input' );
		}
		self::$instances[$format][$value] = $this;
		$this->{$format} = $value;
	}

	/**
	 * Alphanumeric value is all we need to construct a UUID object; saving
	 * anything more is just wasted storage/bandwidth.
	 *
	 * @return string[]
	 */
	public function __sleep() {
		// ensure alphadecimal is populated
		$this->getAlphadecimal();
		return array( 'alphadecimalValue' );
	}

	public function __wakeup() {
		// some B/C code
		// if we have outdated data, correct it and purge all other properties
		if ( $this->binaryValue && strlen( $this->binaryValue ) !== self::BIN_LEN ) {
			$this->binaryValue = substr( $this->binaryValue, 0, self::BIN_LEN );
			$this->hexValue = null;
			$this->alphadecimalValue = null;
		}
		if ( $this->alphadecimalValue ) {
			// Bug 71377 was writing invalid uuid's into cache with an upper cased first letter.  We
			// added code in the constructor to prevent them from being created, but since this is
			// coming from cache lets just fix them and move on with the request.
			// We don't do a comparison first since we would have to lowercase the string to check
			// anyways.
			$this->alphadecimalValue = strtolower( $this->alphadecimalValue );
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
		if ( is_string( $input ) || is_int( $input ) || $input === false ) {
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
			} elseif ( $input instanceof Blob ) {
				return self::create( $input->fetch() );
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
	 * @return mixed
	 */
	public function serializeForApiResult() {
		return $this->getAlphadecimal();
	}

	/**
	 * @return Blob|string UUID encoded in binary format for database storage
	 * @throws FlowException
	 */
	public function getBinary() {
		if ( $this->binaryValue !== null ) {
			return $this->encodeBlob( $this->binaryValue );
		} elseif ( $this->hexValue !== null ) {
			$this->binaryValue = static::hex2bin( $this->hexValue );
		} elseif ( $this->alphadecimalValue !== null ) {
			$this->hexValue = static::alnum2hex( $this->alphadecimalValue );
			self::$instances[self::INPUT_HEX][$this->hexValue] = $this;
			$this->binaryValue = static::hex2bin( $this->hexValue );
		} else {
			throw new FlowException( 'No binary, hex or alphadecimal value available' );
		}
		self::$instances[self::INPUT_BIN][$this->binaryValue] = $this;
		// finally, encode the blob for database storage.  This value
		// may be a Blob object and unusable as an array key.
		return $this->encodeBlob( $this->binaryValue );
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
	 * @throws TimestampException
	 */
	public function getTimestampObj() {
		if ( $this->timestamp === null ) {
			try {
				$this->timestamp = new MWTimestamp( self::hex2timestamp( $this->getHex() ) );
			} catch ( TimestampException $e ) {
				$alnum = $this->getAlphadecimal();
				wfDebugLog( 'Flow', __METHOD__ . ": bogus time value: UUID=$alnum" );
				throw $e;
			}
		}
		return clone $this->timestamp;
	}

	/**
	 * Returns the timestamp in the desired format (defaults to TS_MW)
	 *
	 * @param int $format Desired format (TS_MW, TS_UNIX, etc.)
	 * @return string
	 */
	public function getTimestamp( $format = TS_MW ) {
		$ts = $this->getTimestampObj();
		return $ts->getTimestamp( $format );
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
	 * Takes an array of rows going to/from the database/cache.  Converts uuid and
	 * things that look like uuids into the requested format.
	 *
	 * @param array $array
	 * @param string $format
	 * @return string[]|Blob[] Typically an array of strings.  If required by the database when
	 *  $format === 'binary' uuid values will be represented as Blob objects.
	 */
	public static function convertUUIDs( $array, $format = 'binary' ) {
		$array = ObjectManager::makeArray( $array );
		foreach( $array as $key => $value ) {
			if ( $value instanceof Blob ) {
				// database encoded binary value
				if ( $format === 'alphadecimal' ) {
					$array[$key] = UUID::create( $value->fetch() )->getAlphadecimal();
				}
			} elseif ( $value instanceof UUID ) {
				if ( $format === 'binary' ) {
					$array[$key] = $value->getBinary();
				} elseif ( $format === 'alphadecimal' ) {
					$array[$key] = $value->getAlphadecimal();
				}
			} elseif ( is_string( $value ) && substr( $key, -3 ) === '_id' ) {
				// things that look like uuids
				$len = strlen( $value );
				if ( $format === 'alphadecimal' && $len === self::BIN_LEN ) {
					$array[$key] = UUID::create( $value )->getAlphadecimal();
				} elseif ( $format === 'binary' && (
					( $len >= self::MIN_ALNUM_LEN && $len <= self::ALNUM_LEN )
					||
					$len === self::HEX_LEN
				) ) {
					// Note that if a value is a binary string, but needs to be encoded
					// for the database, that is unhandled here.  A patch is under
					// consideration to allow binary data to always be wrapped in a Blob
					// to clear up this inconsistency.
					$array[$key] = UUID::create( $value )->getBinary();
				}
			}
		}

		return $array;
	}

	/**
	 * @param UUID|null $other
	 * @return boolean
	 */
	public function equals( UUID $other = null ) {
		return $other && $other->getAlphadecimal() === $this->getAlphadecimal();
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

	/**
	 * Converts a binary uuid into a MWTimestamp. This UUID must have
	 * been generated with \UIDGenerator::newTimestampedUID88.
	 *
	 * @param string $hex
	 * @return integer Number of seconds since epoch
	 */
	public static function hex2timestamp( $hex ) {
		$msTimestamp = hexdec( substr( $hex, 0, 12 ) ) >> 2;
		return intval( $msTimestamp / 1000 );
	}

	/**
	 * encode a binary string for database storage
	 *
	 * @param string
	 * @return Blob|string
	 */
	protected function encodeBlob( $binary ) {
		static $dbr;
		if ( $dbr === null ) {
			// assume the any potential database we connect to is
			// the same as this slave.
			$dbr = wfGetDB( DB_SLAVE );
		}
		return $dbr->encodeBlob( $binary );
	}
}
