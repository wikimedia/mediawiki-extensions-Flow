<?php

namespace Flow\Template;

use Flow\Exception\InvalidDataException;

class Locator {

	public function __construct( array $namespaces ) {
		foreach ( $namespaces as $ns => $path ) {
			$this->addNamespace( $ns, $path );
		}
	}

	public function addNamespace( $ns, $path ) {
		$this->namespaces[$ns] = rtrim( $path, '/' );
	}

	public function find( $file ) {
		if ( false === strpos( $file, ':' ) ) {
			return $file;
		}
		list( $ns, $file ) = explode( ':', $file, 2 );
		if ( !isset( $this->namespaces[$ns] ) ) {
			throw new InvalidDataException( 'Unknown template namespace', 'fail-load-data' );
		}

		return $this->namespaces[$ns] . '/' . ltrim( $file, '/' );
	}
}
