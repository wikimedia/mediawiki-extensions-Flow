<?php

namespace Flow\Template;

class ErrorHelper {

	/**
	 * Generic error output for block errors
	 */
	public function block( $block ) {
		if ( !$block->hasErrors() ) {
			return new HtmlString( '' );
		}
		if ( $block instanceof Escaper ) {
			$block = $block->__raw();
		}
		foreach ( $block->getErrors() as $error ) {
			$errors[] = $block->getErrorMessage( $error )->escaped();
		}
		return new HtmlString( '<ul>' . implode( '</li><li>', $errors ) . '</ul>' );
	}
}
