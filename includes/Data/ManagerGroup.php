<?php

namespace Flow\Data;

use Flow\Container;
use Flow\Exception\DataModelException;

/**
 * A little glue code to allow passing around and manipulating multiple
 * ObjectManagers more conveniently.
 */
class ManagerGroup {

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var string[] Map from FQCN or short name to key in container that holds
	 *  the relevant ObjectManager
	 */
	protected $classMap;

	/**
	 * @var string[] List of container keys that have been used
	 */
	protected $used = array();

	public function __construct( Container $container, array $classMap ) {
		$this->container = $container;
		$this->classMap = $classMap;
	}

	/**
	 * Runs ObjectManager:;clear on all managers that have been accessed since
	 * the last clear.
	 */
	public function clear() {
		foreach ( array_keys( $this->used ) as $key ) {
			$this->container[$key]->clear();
		}
		$this->used = array();
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
		$key = $this->classMap[$className];
		$this->used[$key] = true;

		return $this->container[$key];
	}

	public function put( $object, array $metadata ) {
		$this->getStorage( get_class( $object ) )->put( $object, $metadata );
	}


	protected function multiMethod( $method, $objects, array $metadata ) {
		$itemsByClass = array();

		foreach( $objects as $object ) {
			$itemsByClass[ get_class( $object ) ][] = $object;
		}

		foreach( $itemsByClass as $class => $myObjects ) {
			$this->getStorage( $class )->$method( $myObjects, $metadata );
		}
	}

	public function multiPut( $objects, array $metadata = array() ) {
		$this->multiMethod( 'multiPut', $objects, $metadata );
	}

	public function multiRemove( $objects, array $metadata = array() ) {
		$this->multiMethod( 'multiRemove', $objects, $metadata );
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
