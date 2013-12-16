<?php

namespace Flow;

class Container extends \Pimple {
	/**
	 * Get a Flow Container
	 * IMPORTANT: If you are using this function, consider if you can achieve
	 *  your objectives by passing values from an existing, accessible
	 *  container object instead.
	 * If you use this function outside a Flow entry point (such as a hook,
	 *  special page or API module), there is a good chance that your code
	 *  requires refactoring
	 *
	 * @return Flow\Container
	 */
	public static function getContainer() {
		static $container;
		if ( $container === null ) {
			$container = include __DIR__ . '/../container.php';
		}
		return $container;
	}

	/**
	 * Get a specific item from the Flow Container.
	 * This should only be used from entry points (hooks and such) into flow from mediawiki core.
	 */
	public static function get( $name ) {
		$container = self::getContainer();
		return $container[$name];
	}
}
