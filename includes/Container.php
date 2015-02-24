<?php

namespace Flow;

class Container extends \Pimple\Container {
	static private $container;

	/**
	 * Get a Flow Container
	 * IMPORTANT: If you are using this function, consider if you can achieve
	 *  your objectives by passing values from an existing, accessible
	 *  container object instead.
	 * If you use this function outside a Flow entry point (such as a hook,
	 *  special page or API module), there is a good chance that your code
	 *  requires refactoring
	 *
	 * @return Container
	 */
	public static function getContainer() {
		if ( self::$container === null ) {
			if ( defined( 'MW_PHPUNIT_TEST' ) ) {
				$file = 'container-test.php';
			} else {
				$file = 'container.php';
			}
			self::$container = include __DIR__ . "/../$file";
		}
		return self::$container;
	}

	/**
	 * Reset the container, do not use during a normal request.  This is
	 * only for unit tests that need a fresh container.
	 */
	public static function reset() {
		self::$container = null;
	}

	/**
	 * Get a specific item from the Flow Container.
	 * This should only be used from entry points (hooks and such) into flow from mediawiki core.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function get( $name ) {
		$container = self::getContainer();
		return $container[$name];
	}
}
