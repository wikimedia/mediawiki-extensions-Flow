<?php

namespace Flow\Content;

use Flow\Container;
use Title;

abstract class Content {
	static function onGetDefaultModel( Title $title, &$model ) {
		$occupationController = Container::get( 'occupation_controller' );

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$model = 'flow-board';

			return false;
		}

		return true;
	}
}