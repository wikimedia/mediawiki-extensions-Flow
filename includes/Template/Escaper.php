<?php

namespace Flow\Template;

use ArrayAccess;
use ArrayObject;
use Message;

class Escaper implements ArrayAccess {
	/**
	 * @var object
	 */
	protected $object;

	/**
	 * @param object $object
	 */
	public function __construct( $object ) {
		$this->object = $object;
	}

	/**
	 * @param string $prop
	 * @return mixed
	 */
	public function __get( $prop ) {
		return self::__escape( $this->object->$prop );
	}

	/**
	 * @param string $prop
	 * @param mixed $value
	 */
	public function __set( $prop, $value ) {
		$this->object->$prop = $value;
	}

	/**
	 * @param string $prop
	 * @return boolean
	 */
	public function __isset( $prop ) {
		return isset( $this->object->$prop );
	}

	/**
	 * @param string $prop
	 */
	public function __unset( $prop ) {
		unset( $this->object->$key );
	}

	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return self::__escape( call_user_func_array( array( $this->object, $method ), $args ) );
	}

	/**
	 * @param string $offset
	 * @return boolean
	 */
	public function offsetExists( $offset ) {
		return isset( $this->object->$offset );
	}

	/**
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->object->$offset;
	}

	/**
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {
		$this->object->$offset = $vlue;
	}

	/**
	 * @param string $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->object->$offset );
	}

	/**
	 * @return object
	 */
	public function __raw() {
		return $this->object;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	static public function __escape( $value ) {
		if ( $value instanceof OutputString || $value instanceof Message ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$value = new ArrayObject( $value, ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS );
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
