<?php

namespace Flow\Rendering;

/**
 * Interface for an object that generates HTML for a particular UI element.
 *
 * The basic semantics are like so: a fully instantiated UIRenderer represents
 * a single instance of some renderable UI element, including containing all
 * appropriate parameters.
 *
 * That is: any parameters are passed in instantiation, rather than in
 * invocation.
 */
interface UIRenderer {
	/**
	 * Returns the HTML output for this UI element.
	 * @return String|Null     The HTML output.
	 */
	function render();
}

/**
 * Implementation of UIRenderer that is used in Templating for an element that
 * can be instantiated and rendered in a standardised way.
 *
 * Therefore it has a stricter contract: it has to declare and accept
 * parameters.
 */
abstract class UIElement implements UIRenderer {

	/**
	 * Public-facing constructor.
	 * @param array $params Raw parameters
	 */
	public function __construct( array $params ) {
		$realParams = $this->processParameters( $params );

		$this->parameters = $realParams;
		$this->instantiate( $realParams );
	}

	protected function getParameters() {
		return $this->parameters;
	}

	/**
	 * Takes in raw parameters, validates them, and applies
	 * default values.
	 * @param  array  $params Parameters passed by the caller.
	 * @return array          Validated and cleaned parameters.
	 */
	protected function processParameters( array $paramInput ) {
		$validParams = $this->getValidParameters();
		$realParams = array();
		$params = array();

		// Lowercase all param names
		foreach( $paramInput as $key => $value ) {
			$params[strtolower( $key )] = $value;
		}

		foreach( $validParams as $name => $info ) {
			$lName = strtolower( $name );
			if ( isset( $params[$lName] ) ) {
				$realParams[$name] = $params[$lName];
			} elseif ( isset( $info['required'] ) && $info['required'] ) {
				throw new \MWException( "Parameter $name is required" );
			} elseif ( isset( $info['default'] ) ) {
				$realParams[$name] = $info['default'];
			} else {
				$realParams[$name] = null;
			}
		}

		return $realParams;
	}

	/**
	 * Sets up this UIElement from the given params.
	 *
	 * Guaranteed to be called from the constructor.
	 * @param  array $params  Parameter values, after being processed for
	 * default values and so on.
	 */
	abstract protected function instantiate( array $params );

	/**
	 * Returns a list of valid parameters for this UIElement
	 * @return array	An associative array of valid parameters.
	 *
	 * Each entry has the following possible keys:
	 * default: The default value for this parameter.
	 * description: Optional description for this parameter.
	 * required: If set to true, this parameter is required.
	 */
	abstract function getValidParameters();
}

class UIElementFactory {
	function __construct( $elements ) {
		$this->elements = $elements;
	}

	/**
	 * Instantiates a named UIElement
	 * @param  string $elementName Key to $wgFlowUIElements
	 * @param  array  $params      List of parameters
	 * @return UIElement           A UIElement object ready to render
	 */
	public function getElement( $elementName, array $params ) {
		if ( ! isset( $this->elements[$elementName] ) ) {
			throw new \MWException( "Invalid element key" );
		}

		$descriptor = $this->elements[$elementName];
		$class = $descriptor['class'];
		$element = new $class( $params + $descriptor );

		return $element;
	}
}