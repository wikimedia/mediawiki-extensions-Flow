<?php

namespace Flow\Template;

/**
 * Wraps strings with an api providing the same escaping
 * expectations as Message
 */
abstract class OutputString {
	/**
	 * @var string
	 */
	protected $string;

	/**
	 * @param string $string
	 */
	public function __construct( $string ) {
		$this->string = $string;
	}

	/**
	 * @return string Enclosed string sanitized for HTML output
	 */
	abstract function sanitized();

	/**
	 * @return string Bare enclosed string
	 */
	public function __raw() {
		return $this->string;
	}

	/**
	 * only for api compat with Message
	 * @return string Bare enclosed string
	 */
	public function text() {
		return $this->__raw();
	}

	/**
	 * only for api compat with Message
	 * @return string Enclosed string sanitized for HTML output
	 */
	public function escaped() {
		return $this->sanitized();
	}

	/**
	 * only for api compat with Message
	 * @return string Enclosed string sanitized for HTML output
	 */
	public function parse() {
		return $this->sanitized();
	}
}

/**
 * Wraps a plain text string that needs to be sanitized
 */
class TextString extends OutputString {

	/**
	 * @return string Enclosed string sanitized for HTML output
	 */
	public function sanitized() {
		return htmlspecialchars( $this->string );
	}
}

/**
 * Wraps a pre-sanitized string of html
 */
class HtmlString extends OutputString {

	/**
	 * @param string $string A pre-sanitized string of html
	 * @param string $source Source of the html string, used only for debugging
	 */
	public function __construct( $string, $source = null ) {
		parent::__construct( $string );
		$this->source = $source;
	}

	/**
	 * @return string Bare enclosed string
	 */
	public function text() {
		wfDebugLog( 'Flow', __METHOD__ . " :{$this->source}: Potential double escape" );
		return $this->string;
	}

	/**
	 * @return string Enclosed string sanitized for HTML output
	 */
	public function sanitized() {
		return (string)$this->string;
	}
}

