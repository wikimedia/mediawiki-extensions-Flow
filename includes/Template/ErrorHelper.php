<?php

namespace Flow\Template;

class ErrorHelper {

	/**
	 * Generic error output for block errors
	 *
	 * @param Block $block
	 * @return HtmlString
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
		return new HtmlString( '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>' );
	}
}
