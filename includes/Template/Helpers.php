<?php

namespace Flow\Template;

use Flow\Exception\InvalidDataException;

class Helpers {

	public function __construct( array $methods = array() ) {
		$this->methods = $methods;
	}

	public function get( $name ) {
		if ( !isset( $this->methods[$name] ) ) {
			throw new InvalidDataException( 'Unknown template helper ', 'fail-load-data' );
		}
		$helper = $this->methods[$name];
		if ( $helper instanceof Closure ) {
			return $helper();
		} else {
			return $helper;
		}
	}
}
