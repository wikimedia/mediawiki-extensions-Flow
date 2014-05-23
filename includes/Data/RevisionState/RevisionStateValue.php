<?php

namespace Flow\Data\RevisionState;

/**
 * Static entity for holding revision state values and other useful meta data
 */
class RevisionStateValue {

	/**
	 * List of available flow revision state
	 */
	const NONE = '';
	const HIDDEN = 'hide';
	const DELETED = 'delete';
	const SUPPRESSED = 'suppress';
	const CLOSED =  'close';
	const USER_DELETED = 'user-delete';
	const USER_SUPPRESSED = 'user-suppress';
	const COMMENT_DELETED = 'comment-delete';
	const COMMENT_SUPPRESSED = 'comment-suppress';

	/**
	 * The context for the list of available revision states. A revision could
	 * only have one state on a context, eg, a comment can't be both deleted
	 * and suppressed
	 *
	 * @var string[] array( state => the context of the state )
	 **/
	public static $context = array(
		self::NONE => 'revision',
		self::HIDDEN => 'content',
		self::DELETED => 'content',
		self::SUPPRESSED => 'content',
		self::CLOSED => 'revision',
		self::USER_DELETED => 'user',
		self::USER_SUPPRESSED => 'user',
		self::COMMENT_DELETED => 'comment',
		self::COMMENT_SUPPRESSED => 'comment'
	);

	/**
	 * List of flagging state
	 */
	public static $flagState = array(
		self::HIDDEN,
		self::CLOSED
	);

	/**
	 * List of moderation state
	 */
	public static $moderateState = array(
		self::DELETED,
		self::SUPPRESSED,
		self::USER_DELETED,
		self::USER_SUPPRESSED,
		self::COMMENT_DELETED,
		self::COMMENT_SUPPRESSED
	);

}
