<?php

namespace Flow;

use Flow\Exception\RuntimeException;
use OutputPage;

/**
 * Provides a method for rendering a template implemented in raw php.
 * Values passed into the template are accessed through $this->varname
 * within the template file.
 *
 * A map of prefix's to directories can be provided in the constructor
 * as the $namespaces parameter.  This are then used when calling render
 * or partial to provide the correct directory
 *
 *   $template->render( 'flow:post.html.php' );
 *
 * Values passed to render or partial to be included in the template are
 * accessed through property access on $this within the template. All values
 * in the template are explicitly escaped. You must call either ->sanitized()
 * or ->__raw() on the value.  Also currently providing text/escaped/parse
 * methods so the strings can be used in place of a wfMessage call.
 *
 *   <?php echo $this->username->escaped() ?>
 *
 * The escaper is recursive.  When wraping an object all methods have
 * their return value escaped, all property access is escaped.
 *
 *   <?php echo $this->post->getCreatorId()->escaped() ?>
 *   <?php echo $this->post->creatorId->escaped() ?>
 *
 * Helper methods can be added via the constructor.  Helpers must be
 * callable. It is suggested to lazy load objects instead of creating
 * every possible helper ahead of time.  The \Pimple::share method will
 * ensure the inner callback is only called a single time, and the object
 * will only be created when needed.
 *
 *   $helpers = array( 'error' => \Pimple::share( function() { return new ErrorHelper; } ) );
 *   $template = new Template( array(), $helpers );
 *
 * Helpers are called for all undefined method calls.  Helper return values
 * receive the same recursive escaping treatment as passed variables.
 *
 *   <?php echo $this->error()->block( $block )->escaped() ?>
 *
 * Due to how arrays work in php array keys cannot be escaped, but
 * echoing a value without the ->escaped() or ->sanitized() call is
 * obvious to a reviewer.  Array values receive the standard recursive
 * escaping. The proper way to output an array key within a template is:
 *
 *   <?php foreach( $this->posts as $id => $post ): ?>
 *       ...
 *       <?php echo $this->escape( $id )->escaped() ?>
 *       ...
 *   <?php endforeach ?>
 *
 * This wrapping makes some things more difficult, like passing a string
 * into a template to be included in a wfMessage call.  A __raw() method is
 * provided to unwrap the escaped values.
 *
 *   <?php echo wfMessage( 'flow-reply-link', $this->username->__raw() )->escaped() ?>
 *
 */

class Template {

	/**
	 * @var string[] Map from prefix to path
	 */
	protected $namespaces;

	/**
	 * @var callable[] Map from name of helper to callable that returns it
	 */
	protected $helpers;

	/**
	 * @var array The raw unescaped template data
	 */
	protected $data;

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
	public function __construct( array $namespaces = array(), array $helpers = array(), array $data = array(), OutputPage $output = null ) {
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
		return $this->escape( $this->data[$prop] );
	}

	/**
	 * @param string $prop
	 * @param mixed $value
	 */
	public function __set( $prop, $value ) {
		$this->data[$prop] = $value;
	}

	/**
	 * @param string $prop
	 * @return boolean
	 */
	public function __isset( $prop ) {
		return isset( $this->data[$prop] );
	}

	/**
	 * @param string $prop
	 */
	public function __unset( $prop ) {
		unset( $this->data[$prop] );
	}

	/**
	 * Call a helper method
	 *
	 * @param string $name Name of the helper
	 * @param array $args Arguments to pass to the helper
	 * @return mixed
	 * @throws RuntimeException When an unknown helper is requested
	 */
	public function __call( $name, $args ) {
		if ( !isset( $this->helpers[$name] ) ) {
			throw new RuntimeException( 'Unknown template helper: ' . $name, 'other' );
		}
		return $this->escape( call_user_func_array( $this->helpers[$name], $args ) );
	}

	/**
	 * @return array
	 */
	public function __raw() {
		return $this->data;
	}

	public function escape( $value ) {
		return Template\Escaper::__escape( $value );
	}

	/**
	 * @param array $data
	 * @return Template
	 */
	public function addData( array $data ) {
		return $this->setData( $data + $this->data );
	}

	/**
	 * @param array $data
	 * @return Template
	 */
	public function setData( array $data ) {
		$this->data = $data;
		return $this;
	}

	/**
	 * @param string $file optionally prefixed with namespace
	 * @return string full path to the requested file
	 * @throws RuntimeException When prefixed namespace does not exist
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
			return '';
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

