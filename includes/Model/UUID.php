<?php

namespace Flow\Model;

class UUID {
	protected $binaryValue;

	function __construct( $input = false ) {
		if ( is_object( $input ) ) {
			if ( $input instanceof UUID ) {
				$this->binaryValue = $input->getBinary();
			} else {
				throw new MWException( "Got unknown input of type " . get_class( $input ) );
			}
		} elseif ( strlen($input) == 16 ) {
			$this->binaryValue = $input;
		} elseif ( strlen($input) == 32 && preg_match( '/^[a-fA-F0-9]+$/', $input ) ) {
			$this->binaryValue = pack( 'H*', $input );
		} elseif ( is_numeric( $input ) ) {
			$this->binaryValue = pack( 'H*', wfBaseConvert( $input, 10, 16 ) );
		} elseif ( $input === false ) {
			$this->binaryValue = pack( 'H*', \UIDGenerator::newTimestampedUID128( 16 ) );
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
}