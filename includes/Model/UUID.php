<?php

namespace Flow\Model;

use Flow\Data\ObjectManager;
use MWException;

class UUID {
	// provided binary UUID
	protected $binaryValue;
	// alternate representations
	protected $hexValue;
	protected $timestamp;

	// UUID length in hex
	const HEX_LEN = 32;
	// UUID length in binary
	const BIN_LEN = 16;

	function __construct( $binaryValue ) {
		if ( strlen( $binaryValue ) !== self::BIN_LEN ) {
			throw new \InvalidArgumentException( 'Expected ' . self::BIN_LEN . 'char binary string, got: ' . $binaryValue );
		}
		$this->binaryValue = $binaryValue;
	}

	static public function create( $input = false ) {
		$binaryValue = null;
		$hexValue = null;

		if ( is_object( $input ) ) {
			if ( $input instanceof UUID ) {
				return clone $input;
			} else {
				throw new MWException( "Got unknown input of type " . get_class( $input ) );
			}
		} elseif ( $input === null ) {
			return null;
		} elseif ( $input === false ) {
			$hexValue = str_pad( \UIDGenerator::newTimestampedUID128( 16 ), self::HEX_LEN, '0', STR_PAD_LEFT );
		} elseif ( !is_string( $input ) && !is_int( $input ) ) {
			throw new \MWException( "Unknown input type to UUID class: " . gettype( $input ) );
		} elseif ( strlen( $input ) == self::BIN_LEN ) {
			$binaryValue = $input;
		} elseif ( strlen( $input ) == self::HEX_LEN && preg_match( '/^[a-fA-F0-9]+$/', $input ) ) {
			$hexValue = $input;
		} elseif ( is_numeric( $input ) ) {
			$hexValue = wfBaseConvert( $input, 10, 16, self::HEX_LEN );
		} else {
			throw new \MWException( "Unknown input to UUID class" );
		}

		if ( $binaryValue === null && $hexValue !== null ) {
			$binaryValue = pack( 'H*', $hexValue );
		}

		$uuid = new self( $binaryValue );
		$uuid->hexValue = $hexValue;

		return $uuid;
	}

	public function __toString() {
		return $this->getHex();
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
}
