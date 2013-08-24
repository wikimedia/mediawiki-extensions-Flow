<?php

namespace Flow;

class Container extends \Pimple {
	public static function getContainer() {
		static $container;
		if ( $container === null ) {
			$container = include __DIR__ . '/../container.php';
		}
		return $container;
	}
}
