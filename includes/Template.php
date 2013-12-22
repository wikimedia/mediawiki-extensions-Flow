<?php

namespace Flow;

class TemplateFactory {
	public function __construct( Template\Locator $locator, Template\Helpers $helpers ) {
		$this->locator = $locator;
		$this->helpers = $helpers;
	}

	public function create() {
		return new Template( $this->locator, $this->helpers );
	}
}

class Template {

	/**
	 * @var Template\Locator
	 */
	protected $locator;

	/**
	 * @var Template\Helpers
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

	public function __construct( Template\Locator $locator, Template\Helpers $helpers ) {
		$this->locator = $locator;
		$this->helpers = $helpers;
		$this->setData( array() );
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
		return call_user_func_array( $this->helpers->get( $name ), $args );
	}

	public function addData( array $data ) {
		$this->data = array_merge( $this->data, $data );
		$this->escaper = Template\Escaper::__escape( $this->data );
	}

	public function setData( array $data ) {
		$this->data = $data;
		$this->escaper = Template\Escaper::__escape( $this->data );
	}

	public function getData() {
		return $this->data;
	}

	public function render( $name ) {
		ob_start();
		require $this->locator->find( $name );
		return ob_get_clean();
	}

	public function partial( $name, array $data = null ) {
		$partial = clone $this;
		if ( $data !== null ) {
			$partial->setData( $data );
		}
		$partial->render( $name );
	}
}

