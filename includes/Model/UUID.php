<?php

namespace Flow\Model;

use Flow\Data\ObjectManager;

class UUID {
	protected $binaryValue;

	function __construct( $binaryValue ) {
		if ( strlen( $binaryValue ) !== 16 ) {
			throw new \InvalidArgumentException( 'Expected 16 char binary string, got: ' . $binaryValue );
		}
		$this->binaryValue = $binaryValue;
	}

	static public function create( $input = false ) {
		if ( is_object( $input ) ) {
			if ( $input instanceof UUID ) {
				return new self( $input->getBinary() );
			} else {
				throw new MWException( "Got unknown input of type " . get_class( $input ) );
			}
		} elseif ( strlen( $input ) == 16 ) {
			return new self( $input );
		} elseif ( strlen( $input ) == 32 && preg_match( '/^[a-fA-F0-9]+$/', $input ) ) {
			return new self( pack( 'H*', $input ) );
		} elseif ( is_numeric( $input ) ) {
			return new self( pack( 'H*', wfBaseConvert( $input, 10, 16 ) ) );
		} elseif ( $input === false ) {
			return new self( pack( 'H*', \UIDGenerator::newTimestampedUID128( 16 ) ) );
		} elseif ( $input === null ) {
			return null;
		} else {
			throw new \MWException( "Unknown input to UUID class" );
		}
	}

	public function __toString() {
		echo "<p>Attempt to use UUID object as string</p>\n";
		echo "<p>UUID: ". $this->getHex() . "</p>";
		echo wfBacktrace();
		die;

		// return $this->getHex();
	}

	public function getHex() {
		return bin2hex( $this->binaryValue );
	}

	public function getBinary() {
		return $this->binaryValue;
	}

	public function getNumber() {
		return wfBaseConvert( $this->getHex(), 16, 10 );
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
