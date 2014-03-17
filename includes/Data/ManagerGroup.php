<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Exception\DataModelException;

/**
 * A little glue code to allow passing around and manipulating multiple
 * ObjectManagers more convenient.
 */
class ManagerGroup {
	public function __construct( Container $container, array $classMap ) {
		$this->container = $container;
		$this->classMap = $classMap;
	}

	/**
	 * @param string $className
	 * @return ObjectManager
	 * @throws DataModelException
	 */
	public function getStorage( $className ) {
		if ( !isset( $this->classMap[$className] ) ) {
			throw new DataModelException( "Request for '$className' is not in classmap: " . implode( ', ', array_keys( $this->classMap ) ), 'process-data' );
		}

		return $this->container[$this->classMap[$className]];
	}

	public function put( $object ) {
		$this->getStorage( get_class( $object ) )->put( $object );
	}

	protected function call( $method, $args ) {
		$className = array_shift( $args );

		return call_user_func_array(
			array( $this->getStorage( $className ), $method ),
			$args
		);
	}

	public function get( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function getMulti( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function find( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function findMulti( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function found( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}

	public function foundMulti( /* ... */ ) {
		return $this->call( __FUNCTION__, func_get_args() );
	}
}
