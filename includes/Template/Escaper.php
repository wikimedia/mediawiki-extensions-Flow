<?php

namespace Flow\Template;

use ArrayAccess;
use ArrayIterator;
use ArrayObject;
use IteratorAggregate;
use Message;
use Flow\Exception\RuntimeException;

/**
 * This class wraps all values that are unsafe for output to the user
 * with the OutputString interface.  The core Message class doesn't
 * explicitly implement OutputString, but it has all the correct methods.
 *
 * If the value to be escaped is a complex data type(object|array) this
 * will return an Escaper instance wrapping that complex data type. All
 * php magic methods are implemented such that the escaped object can be
 * used the same as if it were unescaped, with the only difference being
 * that all properties and method return values pass through the escaping
 * function.
 *
 * NOTE: Array keys cannot be wrapped in an OutputString object.  Any
 * array key to be output must be explicitly escaped.
 * NOTE: Most array functions, like array_keys will not work. Usage of
 * arrays in templates is limited to foreach and array access 
 *
 * Usage:
 *    $escaped = Flow\Template\Escaper::__escape( $anything );
 */
class Escaper implements ArrayAccess, IteratorAggregate {
	/**
	 * @var object The object being escaped
	 */
	protected $object;

	/**
	 * @param object $object The object to escape
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
		unset( $this->object->$prop );
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
		return isset( $this->object[$offset] );
	}

	/**
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return self::__escape( $this->object[$offset] );
	}

	/**
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {
		$this->object[$offset] = $value;
	}

	/**
	 * @param string $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->object[$offset] );
	}

	/**
	 * Return the unescaped object being escaped.  Note that if an array
	 * was passed in an ArrayObject comes back out through this method.
	 * This method gets a double underscore prefix to avoid conflicting
	 * with methods in the wrapped object.
	 *
	 * @return object
	 */
	public function __raw() {
		return $this->object;
	}

	/**
	 * Returns an escaped value. This method gets a double underscore
	 * prefix to avoid conflicting with methods in the wrapped object.
	 *
	 * NOTE: Array keys are not handled, if used as part of the output
	 * they must be independantly escaped.
	 * NOTE: Although Message doesn't implement OutputString, it matches
	 * the interface.
	 *
	 * @param mixed $value The value to escape. If the value is already
	 * an OutputString or Message instance then it is already escaped
	 * and returned as is.  If the value is an array or an object it
	 * is wrapped with this Escaper object.  Booleans and nulls pass through
	 * unchanged.  Everything else is wrapped in the TextString class
	 * which implements OutputString to properly escape the values.
	 *
	 * @return Escaper|OutputString|Message
	 */
	static public function __escape( $value ) {
		if ( $value instanceof OutputString || $value instanceof Message ) {
			return $value;
		}

		// arrays must be wrapped, ensuring that even values added to the
		// array after escaping receive sanitization.
		if ( is_object( $value ) || is_array( $value ) ) {
			return new self( $value );
		}

		// don't cast null to string otherwise object|null
		// becomes object|string when using __raw
		if ( is_null( $value ) ) {
			return new TextString( $value );
		}

		return new TextString( (string)$value );
	}

	public function getIterator() {
		if ( !is_array( $this->object ) ) {
			throw new RuntimeException( 'Can only currently iterate arrays' );
		}
		return new ArrayIterator( array_map( array( 'Flow\Template\Escaper', '__escape' ), $this->object ) );
	}
}
