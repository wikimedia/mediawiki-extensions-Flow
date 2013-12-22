<?php

namespace Flow\Template;

use ArrayAccess;
use ArrayObject;
use Message;

class Escaper implements ArrayAccess {
	public function __construct( $object ) {
		$this->object = $object;
	}

	public function __get( $prop ) {
		return self::__escape( $this->object->$prop );
	}

	public function __set( $prop, $value ) {
		$this->object->$prop = $value;
	}

	public function __isset( $prop ) {
		return isset( $this->object->$prop );
	}

	public function __unset( $prop ) {
		unset( $this->object->$key );
	}

	public function offsetExists( $offset ) {
		return isset( $this->object->$offset );
	}

	public function offsetGet( $offset ) {
		return $this->object->$offset;
	}

	public function offsetSet( $offset, $value ) {
		$this->object->$offset = $vlue;
	}

	public function offsetUnset( $offset ) {
		unset( $this->object->$offset );
	}

	public function __raw() {
		return $this->object;
	}

	static public function __escape( $value ) {
		if ( $value instanceof OutputString || $value instanceof Message ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$value = new ArrayObject( $value );
		}

		if ( is_object( $value ) ) {
			return new self( $value );
		}

		if ( is_string( $value ) ) {
			return new TextString( $value );
		}

		// Not an array, string, or object.  Pass through
		return $value;
	}
}
