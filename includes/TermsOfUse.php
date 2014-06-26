<?php

namespace Flow;

use Flow\Exception\InvalidInputException;

/**
 * A helper class that returns the terms of use to be shown when adding new
 * topics and replies and editing existing topics, replies and the board header
 */
class TermsOfUse {

	/**
	 * List of terms of use with corresponding message keys
	 */
	private static $terms = array(
		'new-topic' => 'flow-terms-of-use-new-topic',
		'reply' => 'flow-terms-of-use-reply',
		'edit' => 'flow-terms-of-use-edit',
		'summarize' => 'flow-terms-of-use-summarize',
		'close-topic' => 'flow-terms-of-use-close-topic',
		'reopen-topic' => 'flow-terms-of-use-reopen-topic'
	);

	/**
	 * Give a chance for other extensions to specify alternative message strings
	 * and retrieve and parse the message strings
	 */
	public static function setTerms() {
		wfRunHooks(
			'FlowTermsOfUse',
			array(
				&self::$terms['new-topic'],
				&self::$terms['reply'],
				&self::$terms['edit'],
				&self::$terms['summarize'],
				&self::$terms['close-topic'],
				&self::$terms['reopen-topic']
			)
		);
	}

	/**
	 * Get terms of use
	 * @param string The key for the term of use
	 * @return string
	 */
	public static function getTerm( $name = '' ) {
		if ( $name && !isset( self::$terms[$name] ) ) {
			throw new InvalidInputException( 'Term ' . $name . ' does not exist', 'invalid-input' );
		}

		static $runHook = false;
		if ( !$runHook ) {
			self::setTerms();
			$runHook = true;
		}

		if ( $name ) {
			return self::$terms[$name];
		} else {
			return self::$terms;
		}
	}

}
