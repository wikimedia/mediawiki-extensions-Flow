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
	 * @var string
	 */
	private static $summarizeTerms;

	/**
	 * @var string
	 */
	private static $closeTopicTerms;

	/**
	 * @var string
	 */
	private static $reopenTopicTerms;

	/**
	 * Give a chance for other extensions to specify alternative message strings
	 * and retrieve and parse the message strings
	 */
	private static function setTerms() {
		$addTopicKey = 'flow-terms-of-use-new-topic';
		$replyKey = 'flow-terms-of-use-reply';
		$editKey = 'flow-terms-of-use-edit';
		$summarizeKey = 'flow-terms-of-use-summarize';
		$closeTopicKey = 'flow-terms-of-use-close-topic';
		$reopenTopicKey = 'flow-terms-of-use-reopen-topic';

		// This is getting out of control, maybe we should consider having
		// one generic message for submitting any user form content, like:
		// by submitting the content, you agree blah...
		wfRunHooks( 'FlowTermsOfUse', array( &$addTopicKey, &$replyKey, &$editKey, &$summarizeKey, &$closeTopicKey, &$reopenTopicKey ) );

		self::$addTopicTerms = wfMessage( $addTopicKey )->parse();
		self::$replyTerms = wfMessage( $replyKey )->parse();
		self::$editTerms = wfMessage( $editKey )->parse();
		self::$summarizeTerms = wfMessage( $summarizeKey )->parse();
		self::$closeTopicTerms = wfMessage( $closeTopicKey )->parse();
		self::$reopenTopicTerms = wfMessage( $reopenTopicKey )->parse();
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

	/**
	 * @return string
	 */
	public static function getSummarizeTerms() {
		if ( self::$summarizeTerms === null ) {
			self::setTerms();
		}
		return self::$summarizeTerms;
	}

	/**
	 * @return string
	 */
	public static function getCloseTopicTerms() {
		if ( self::$closeTopicTerms === null ) {
			self::setTerms();
		}
		return self::$closeTopicTerms;
	}

	/**
	 * @return string
	 */
	public static function getReopenTopicTerms() {
		if ( self::$reopenTopicTerms === null ) {
			self::setTerms();
		}
		return self::$reopenTopicTerms;
	}

}
