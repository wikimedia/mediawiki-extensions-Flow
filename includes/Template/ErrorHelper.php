<?php

namespace Flow\Template;

class ErrorHelper {

	/**
	 * Generic error output for block errors
	 *
	 * @param \Flow\Block\Block $block
	 * @return HtmlString
	 */
	public function block( $block ) {
		if ( $block instanceof Escaper ) {
			$block = $block->__raw();
		}
		if ( !$block->hasErrors() ) {
			return '';
		}
		// This needs to use parse, some use wikitext such as
		// https://www.mediawiki.org/wiki/MediaWiki:Abusefilter-disallowed
		$errors = array();
		foreach ( $block->getErrors() as $error ) {
			$errors[] = $block->getErrorMessage( $error )->parse();
		}
		return new HtmlString( '<ul><li>' . implode( '</li><li>', $errors ) . '</li></ul>' );
	}
}
