<?php

namespace Flow;

use Flow\Exception\RuntimeException;
use Closure;
use OutputPage;

class Template {

	/**
	 * @var array Map from prefix to path
	 */
	protected $namespaces;

	/**
	 * @var array Map from name of helper to callable that returns it
	 */
	protected $helpers;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var Template\Escaper
	 */
	protected $escaper;

	/**
	 * @var OutputPage|null
	 */
	protected $output;

	public function __construct( array $namespaces, array $helpers, array $data = array(), OutputPage $output ) {
		foreach ( $namespaces as $ns => $path ) {
			$this->addNamespace( $ns, $path );
		}
		$this->helpers = $helpers;
		$this->output = $output;
		$this->setData( $data );
		$this->output = $output;
	}

	public function __get( $prop ) {
		return $this->escaper->$prop;
	}

	public function __set( $prop, $value ) {
		$this->data->$prop = $value;
	}

	public function __isset( $prop ) {
		return isset( $this->data->$prop );
	}

	public function __unset( $prop ) {
		unset( $this->data->$key );
	}

	public function __call( $name, $args ) {
		return $this->getHelper( $name );
	}

	public function __raw() {
		return $this->data;
	}

	public function addData( array $data ) {
		$this->data = array_merge( $this->data, $data );
		$this->escaper = Template\Escaper::__escape( $this->data );
		return $this;
	}

	public function setData( array $data ) {
		$this->data = $data;
		$this->escaper = Template\Escaper::__escape( $this->data );
		return $this;
	}

	public function getHelper( $name ) {
		if ( !isset( $this->helpers[$name] ) ) {
			throw new RuntimeException( 'Unknown template helper', 'other' );
		}
		// Delay instantiating helpers
		if ( $this->helpers[$name] instanceof Closure ) {
			$this->helpers[$name] = call_user_func( $this->helpers[$name] );
		}
		return $this->helpers[$name];
	}

	public function addNamespace( $ns, $path ) {
		$this->namespaces[$ns] = rtrim( $path, '/' );
	}

	public function findFile( $file ) {
		if ( false === strpos( $file, ':' ) ) {
			return $file;
		}
		list( $ns, $file ) = explode( ':', $file, 2 );
		if ( !isset( $this->namespaces[$ns] ) ) {
			throw new RuntimeException( 'Unknown template namespace', 'other' );
		}

		return $this->namespaces[$ns] . '/' . ltrim( $file, '/' );
	}

	public function render( $__name, array $__data = null, $returnString = false ) {
		if ( $__data !== null ) {
			$this->addData( $__data );
		}
		ob_start();
		require $this->findFile( $__name );
		$output = ob_get_clean();

		if ( $returnString === false ) {
			$this->output->addHTML( $output );
		} else {
			return $output;
		}
	}

	public function partial( $name, array $data = null ) {
		$partial = clone $this;
		if ( $data !== null ) {
			$partial->setData( $data );
		}
		return $partial->render( $name, null, true );
	}

}

