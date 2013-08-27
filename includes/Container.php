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
	 * @return FLow\Container
	 */
	static function getContainer() {
		static $container = false;

		if ( $container == false ) {
			$container = include __DIR__ . '/../container.php';
		}

		return $container;
	}
}
