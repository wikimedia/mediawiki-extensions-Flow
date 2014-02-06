<?php

namespace Flow;

/**
 * A helper class that returns the terms of use to be shown when adding new
 * topics and replies and editing existing topics, replies and the board header
 */
class TermsOfUse {
	/**
	 * @var string
	 */
	private static $addTopicTerms;

	/**
	 * @var string
	 */
	private static $replyTerms;

	/**
	 * @var string
	 */
	private static $editTerms;

	/**
	 * Give a chance for other extensions to specify alternative message strings
	 * and retrieve and parse the message strings
	 */
	private static function setTerms() {
		$addTopicKey = 'flow-terms-of-use-new-topic';
		$replyKey = 'flow-terms-of-use-reply';
		$editKey = 'flow-terms-of-use-edit';

		wfRunHooks( 'FlowTermsOfUse', array( &$addTopicKey, &$replyKey, &$editKey ) );

		self::$addTopicTerms = wfMessage( $addTopicKey )->parse();
		self::$replyTerms = wfMessage( $replyKey )->parse();
		self::$editTerms = wfMessage( $editKey )->parse();
	}

	/**
	 * @return string
	 */
	public static function getAddTopicTerms() {
		if ( self::$addTopicTerms === null ) {
			self::setTerms();
		}
		return self::$addTopicTerms;
	}

	/**
	 * @return string
	 */
	public static function getReplyTerms() {
		if ( self::$replyTerms === null ) {
			self::setTerms();
		}
		return self::$replyTerms;
	}

	/**
	 * @return string
	 */
	public static function getEditTerms() {
		if ( self::$editTerms === null ) {
			self::setTerms();
		}
		return self::$editTerms;
	}
}
