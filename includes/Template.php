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
	 * @var array The dynamic data used in the template.
	 */
	protected $data;

	/**
	 * @var Template\Escaper The templates data wrapped with an escaper.
	 */
	protected $escaper;

	/**
	 * @var OutputPage|null
	 */
	protected $output;

	/**
	 * @param array $namespaces Map from namespace prefix to a directory
	 * @param array $helpers Map from helper name to callable returning the helper
	 * @param array $data The dynamic data used in the template
	 * @param OutputPage $output 
	 */
	public function __construct( array $namespaces, array $helpers, array $data = array(), OutputPage $output = null ) {
		$this->namespaces = $namespaces;
		$this->helpers = $helpers;
		$this->setData( $data );
		$this->output = $output;
	}

	/**
	 * @param string $prop
	 * @return mixed
	 */
	public function __get( $prop ) {
		return $this->escaper->$prop;
	}

	/**
	 * @param string $prop
	 * @param mixed $value
	 */
	public function __set( $prop, $value ) {
		$this->data->$prop = $value;
	}

	/**
	 * @param string $prop
	 * @return boolean
	 */
	public function __isset( $prop ) {
		return isset( $this->data->$prop );
	}

	/**
	 * @param string $prop
	 */
	public function __unset( $prop ) {
		unset( $this->data->$key );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		if ( !isset( $this->helpers[$name] ) ) {
			throw new RuntimeException( 'Unknown template helper', 'other' );
		}
		return Template\Escaper::__escape( call_user_func_array( $this->helpers[$name], $args ) );
	}

	/**
	 * @return array
	 */
	public function __raw() {
		return $this->data;
	}

	/**
	 * @param array $data
	 * @return Template
	 */
	public function addData( array $data ) {
		$this->data = array_merge( $this->data, $data );
		$this->escaper = Template\Escaper::__escape( $this->data );
		return $this;
	}

	/**
	 * @param array $data
	 * @return Template
	 */
	public function setData( array $data ) {
		$this->data = $data;
		$this->escaper = Template\Escaper::__escape( $this->data );
		return $this;
	}

	/**
	 * @param string $file
	 * @return string
	 */
	public function findFile( $file ) {
		if ( false === strpos( $file, ':' ) ) {
			return $file;
		}
		list( $ns, $file ) = explode( ':', $file, 2 );
		if ( !isset( $this->namespaces[$ns] ) ) {
			throw new RuntimeException( 'Unknown template namespace', 'other' );
		}

		return rtrim( $this->namespaces[$ns], '/' ) . '/' . ltrim( $file, '/' );
	}

	/**
	 * @param string $name
	 * @param array $data
	 * @param boolean $returnString
	 * @return string
	 */
	public function render( $name, array $data = null, $returnString = true ) {
		if ( $data !== null ) {
			$this->addData( $data );
		}
		$output = $this->renderInternal( $this->findFile( $name ) );
		if ( $returnString === false ) {
			$this->output->addHTML( $output );
		} else {
			return $output;
		}
	}

	/**
	 * @param string $path
	 * @return string
	 */
	protected function renderInternal( $path ) {
		ob_start();
		require $path;
		return ob_get_clean();
	}

	/**
	 * @param string $name
	 * @param array $data
	 * @return string
	 */
	public function partial( $name, array $data = null ) {
		$partial = clone $this;
		if ( $data !== null ) {
			$partial->setData( $data );
		}
		return $partial->render( $name, null, true );
	}

}

