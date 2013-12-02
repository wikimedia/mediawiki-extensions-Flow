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

	function __construct( $binaryValue ) {
		if ( strlen( $binaryValue ) !== 16 ) {
			throw new \InvalidArgumentException( 'Expected 16 char binary string, got: ' . $binaryValue );
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
			$hexValue = str_pad( \UIDGenerator::newTimestampedUID128( 16 ), 32, '0', STR_PAD_LEFT );
			$binaryValue = pack( 'H*', $hexValue );
		} elseif ( !is_string( $input ) && !is_int( $input ) ) {
			throw new \MWException( "Unknown input type to UUID class: " . gettype( $input ) );
		} elseif ( strlen( $input ) == 16 ) {
			$binaryValue = $input;
		} elseif ( strlen( $input ) == 32 && preg_match( '/^[a-fA-F0-9]+$/', $input ) ) {
			$hexValue = $input;
			$binaryValue = pack( 'H*', $hexValue );
		} elseif ( is_numeric( $input ) ) {
			$hexValue = wfBaseConvert( $input, 10, 16, 32 );
			$binaryValue = pack( 'H*', $hexValue );
		} else {
			throw new \MWException( "Unknown input to UUID class" );
		}

		$uuid = new self( $binaryValue );
		$uuid->hexValue = $hexValue;

		return $uuid;
	}

	public function __toString() {
		echo "<p>Attempt to use UUID object as string</p>\n";
		echo "<p>UUID: ". $this->getHex() . "</p>";
		echo wfBacktrace();
		die;

		// return $this->getHex();
	}

	public function getHex() {
		if ( $this->hexValue === null ) {
			$this->hexValue = str_pad( bin2hex( $this->binaryValue ), 32, '0', STR_PAD_LEFT );
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
