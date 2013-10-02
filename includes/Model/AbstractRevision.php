<?php

namespace Flow\Model;

use MWTimestamp;
use User;
use Flow\ParsoidUtils;

abstract class AbstractRevision {
	const MODERATED_NONE = '';
	const MODERATED_HIDDEN = 'hide';
	const MODERATED_DELETED = 'delete';

	const MODERATED_CENSORED = 'censor';

	static protected $perms = array(
		self::MODERATED_NONE => array(
			'perm' => null,
			'usertext' => null,
			'content' => null
		),
		self::MODERATED_HIDDEN => array(
			'perm' => 'flow-hide',
			'usertext' => 'flow-post-hidden',
			'content' => 'flow-post-hidden-by',
		),
		self::MODERATED_DELETED => array(
			'perm' => 'flow-delete',
			'usertext' => 'flow-post-deleted',
			'content' => 'flow-post-deleted-by',
		),
		self::MODERATED_CENSORED => array(
			'perm' => 'flow-censor',
			'usertext' => 'flow-post-censored',
			'content' => 'flow-post-censored-by',
		),
	);

	protected $revId;
	protected $userId;
	protected $userText;
	protected $flags = array();
	// An i18n message key indicating what kind of change this revision is
	// primary use case is the a revision history list.
	// TODO: i18n key may be too limiting, consider allowing custom revision comments
	protected $comment;
	protected $prevRevision;

	// content
	protected $content;
	// Only populated when external store is in use
	protected $contentUrl;
	// This is decompressed on-demand from $this->content in self::getContent()
	protected $decompressedContent;
	// Converted (wikitext|html) content, based off of $this->decompressedContent
	protected $convertedContent = array();

	// moderation states for the revision.  This is technically denormalized data
	// since it can be overwritten and does not provide a full history.
	// The tricky part is updating moderation is a new revision for hide and
	// delete, but adjusts an existing revision for full censor.
	protected $moderationState = self::MODERATED_NONE;
	protected $moderationTimestamp;
	protected $moderatedByUserId;
	protected $moderatedByUserText;

	protected $lastEditId;
	protected $lastEditUserId;
	protected $lastEditUserText;

	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new static;
		} elseif ( !$obj instanceof static ) {
			throw new \Exception( 'wrong object type' );
		}
		$obj->revId = UUID::create( $row['rev_id'] );
		$obj->userId = $row['rev_user_id'];
		$obj->userText = $row['rev_user_text'];
		$obj->prevRevision = UUID::create( $row['rev_parent_id'] );
		$obj->comment = $row['rev_comment'];
	 	$obj->flags = array_filter( explode( ',', $row['rev_flags'] ) );
		$obj->content = $row['rev_content'];
		// null if external store is not being used
		$obj->contentUrl = $row['rev_content_url'];
		$obj->decompressedContent = null;

		$obj->moderationState = $row['rev_mod_state'];
		$obj->moderatedByUserId = $row['rev_mod_user_id'];
		$obj->moderatedByUserText = $row['rev_mod_user_text'];
		$obj->moderationTimestamp = $row['rev_mod_timestamp'];

		// isset required because there is a possible db migration, cached data will not have it
		$obj->lastEditId = isset( $row['rev_last_edit_id'] ) ? UUID::create( $row['rev_last_edit_id'] ) : null;
		$obj->lastEditUserId = isset( $row['rev_edit_user_id'] ) ? $row['rev_edit_user_id'] : null;
		$obj->lastEditUserText = isset( $row['rev_edit_user_text'] ) ? $row['rev_edit_user_text'] : null;

		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return array(
			'rev_id' => $obj->revId->getBinary(),
			'rev_user_id' => $obj->userId,
			'rev_user_text' => $obj->userText,
			'rev_parent_id' => $obj->prevRevision ? $obj->prevRevision->getBinary() : null,
			'rev_comment' => $obj->comment,
			'rev_type' => $obj->getRevisionType(),

			'rev_content' => $obj->content,
			'rev_content_url' => $obj->contentUrl,
			'rev_flags' => implode( ',', $obj->flags ),

			'rev_mod_state' => $obj->moderationState,
			'rev_mod_user_id' => $obj->moderatedByUserId,
			'rev_mod_user_text' => $obj->moderatedByUserText,
			'rev_mod_timestamp' => $obj->moderationTimestamp,

			'rev_last_edit_id' => $obj->lastEditId ? $obj->lastEditId->getBinary() : null,
			'rev_edit_user_id' => $obj->lastEditUserId,
			'rev_edit_user_text' => $obj->lastEditUserText,
		);
	}

	/**
	 * NOTE: No guarantee is made here regarding if $this is the newest revision.  Validation
	 * must happen externally.  DB *will* throw an exception if this attempts to write to db
	 * and it is not the most recent revision.
	 */
	public function newNullRevision( User $user ) {
		$obj = clone $this;
		$obj->revId = UUID::create();
		$obj->userId = $user->getId();
		$obj->userText = $user->getName();
		$obj->prevRevision = $this->revId;
		return $obj;
	}

	/**
	 * Create the next revision with new content
	 */
	public function newNextRevision( User $user, $content, $comment ) {
		$obj = $this->newNullRevision( $user );
		$obj->setNextContent( $user, $content );
		$obj->comment = $comment;
		return $obj;
	}

	public function moderate( User $user, $state ) {
		$mostRestricted = max( $state, $this->moderationState );
		if ( !$this->isAllowed( $user, $mostRestricted ) ) {
			return null;
		}
		// Censoring is special,  other moderation types just create
		// a new revision but censoring adjusts the existing revision.
		// Yes this mucks with the history just being a revision list.
		if ( $state === self::MODERATED_CENSORED ) {
			$obj = $this;
		} else {
			$obj = $this->newNullRevision( $user );
		}

		$obj->moderationState = $state;
		if ( $state === self::MODERATED_NONE ) {
			$obj->moderatedByUserId = null;
			$obj->moderatedByUserText = null;
			$obj->moderationTimestamp = null;
		} else {
			$obj->moderatedByUserId = $user->getId();
			$obj->moderatedByUserText = $user->getName();
			$obj->moderationTimestamp = wfTimestampNow();
		}
		return $obj;
	}

	public function restore( User $user ) {
		return $this->moderate( $user, self::MODERATED_NONE );
	}

	public function getRevisionId() {
		return $this->revId;
	}

	/**
	 * @param User $user The user requesting access.  When null assumes a user with no permissions.
	 * @param int $state One of the self::MODERATED_* constants. When null the internal moderation state is used.
	 * @return boolean True when the user is allowed to see the current revision
	 */
	// Is the user allowed to see this revision ?
	protected function isAllowed( $user = null, $state = null ) {
		// allowing a $state to be passed is a bit hackish
		if ( $state === null ) {
			$state = $this->moderationState;
		}
		if ( !isset( self::$perms[$state] ) ) {
			throw new \Exception( 'Unknown stored moderation state' );
		}

		$perm = self::$perms[$state]['perm'];
		return $perm === null || ( $user && $user->isAllowed( $perm ) );
	}

	public function getContent( $user = null, $format = 'html' ) {
		if ( $this->isAllowed( $user ) ) {
			return $this->getConvertedContent( $format );
		} else {
			$moderatedAt = new MWTimestamp( $this->moderationTimestamp );

			if ( $this->moderationState === self::MODERATED_CENSORED ) {
				// Censored is based on timestamp of this revision
				$createdAt = $this->revId->getTimestampObj();
			} elseif ( $this->prevRevision ) {
				// Everything else is based on timestamp of previous revision
				$createdAt = $this->prevRevision->getTimestampObj();
			} else {
				// not censored, but this is the first revision.  We should never get here.
				wfDebugLog( __CLASS__, __FUNCTION__ . ': Unreachable condition, un censored but moderated first post : ' . $this->revId->getHex() );
				$createdAt = $this->revId->getTimestampObj();
			}

			// Messages: flow-post-hidden-by, flow-post-deleted-by, flow-post-censored-by
			return wfMessage(
				self::$perms[$this->moderationState]['content'],
				$this->moderatedByUserText,
				// FIXME (spage, 2013-09-13) results in, e.g. "Deleted by Admin In 8 days".
				// Removing createdAt gives right timeframe, but all the $createdAt code
				// above suggests we intend something like "3 days later"?
				$moderatedAt->getHumanTimestamp( $createdAt )
			);
		}
	}

	public function getContentRaw() {
		if ( $this->decompressedContent === null ) {
			$this->decompressedContent = \Revision::decompressRevisionText( $this->content, $this->flags );
		}

		return $this->decompressedContent;
	}

	public function getConvertedContent( $format = 'html' ) {
		if ( !isset( $this->convertedContent[$format] ) ) {
			// check how content is stored & convert to requested format
			$sourceFormat = in_array( 'html', $this->flags ) ? 'html' : 'wikitext';
			$this->convertedContent[$format] = ParsoidUtils::convert( $sourceFormat, $format, $this->getContentRaw() );
		}

		return $this->convertedContent[$format];
	}

	public function getUserId() {
		return $this->userId;
	}

	public function getUserText( $user = null ) {
		if ( $this->isAllowed( $user ) ) {
			return $this->getUserTextRaw();
		} else {
			// Messages: flow-post-hidden, flow-post-deleted, flow-post-censored
			return wfMessage( self::$perms[$this->moderationState]['usertext'] );
		}
	}

	public function getUserTextRaw() {
		return $this->userText;
	}

	/**
	 * Should only be used for setting the initial content.  To set subsequent content
	 * use self::setNextContent
	 *
	 * @param string $content Content in wikitext format
	 * @throws \Exception
	 */
	protected function setContent( $content ) {
		// TODO: How is this guarantee of only receiving wikitext made?
		$inputFormat = 'wikitext';
		if ( $this->content !== null ) {
			throw new \Exception( 'Updating content must use setNextContent method' );
		}
		$this->convertedContent = array( $inputFormat  => $content );

		// convert content to desired storage format
		$storageFormat = $this->getStorageFormat();
		if ( $this->isFormatted() && $storageFormat !== $inputFormat ) {
			$this->convertedContent[$storageFormat] = ParsoidUtils::convert(
				$inputFormat,
				$storageFormat,
				$content
			);
		}

		$this->content = $this->decompressedContent = $this->convertedContent[$storageFormat];
		$this->contentUrl = null;

		// should this only remove a subset of flags?
		$this->flags = array_filter( explode( ',', \Revision::compressRevisionText( $this->content ) ) );
		$this->flags[] = $storageFormat;
	}

	/**
	 * Apply new content to a revision.
	 */
	protected function setNextContent( User $user, $content ) {
		if ( $this->moderationState !== self::MODERATED_NONE ) {
			throw new \Exception( 'Cannot change content of restricted revision' );
		}
		if ( $content !== $this->getContent() ) {
			$this->content = null;
			$this->setContent( $content );
			$this->lastEditId = $this->getRevisionId();
			$this->lastEditUserId = $user->getId();
			$this->lastEditUserText = $user->getName();
		}
	}

	/**
	 * Determines whether this revision contains formatted content
	 * (i.e. content with separate HTML and WikiText representations)
	 * or unformatted content (i.e. one plaintext representation)
	 * Note that this function may return different values for different
	 * instances of the same class.
	 * @return boolean True for formatted, False for plaintext
	 */
	protected function isFormatted() {
		return true;
	}

	/**
	 * Determines the appropriate format to store content in.
	 * Usually, the default storage format, but if isFormatted() returns
	 * false, then it will return 'wikitext'.
	 * @return string The name of the storage format.
	 */
	protected function getStorageFormat() {
		global $wgFlowContentFormat;
		return $this->isFormatted() ? $wgFlowContentFormat : 'wikitext';
	}

	public function getPrevRevisionId() {
		return $this->prevRevision;
	}

	public function getComment() {
		return $this->comment;
	}

	public function getModerationState() {
		return $this->moderationState;
	}

	public function isModerated() {
		return $this->moderationState !== self::MODERATED_NONE;
	}

	public function getModerationTimestamp() {
		return $this->moderationTimestamp;
	}

	/**
	 * @param string|array $flags
	 * @return boolean True when at least one flag in $flags is set
	 */
	public function isFlaggedAny( $flags ) {
		foreach ( (array) $flags as $flag ) {
			if ( false !== array_search( $flag, $this->flags ) ) {
				return true;
			}
		}
		return false;
	}

	public function isFlaggedAll( $flags ) {
		foreach ( (array) $flags as $flag ) {
			if ( false === array_search( $flag, $this->flags ) ) {
				return false;
			}
		}
		return true;
	}

	public function isFirstRevision() {
		return $this->prevRevision === null;
	}

	public function isOriginalContent() {
		return $this->lastEditId === null;
	}

	/**
	 * @param $user User requesting access to last content editor
	 * @return string
	 */
	public function getLastContentEditorName( $user = null ) {
		// TODO: to write this function properly will need to flesh out how
		// oversighting works.  Prefer to create an external security class that is
		// configurable per-wiki, pass revisions into it(or wrap them in it for
		// view objects?) to get possibly protected content.
		if ( $this->isAllowed( $user ) ) {
			return $this->lastEditUserText;
		} else {
			return '';
		}
	}

	public function getLastContentEditId() {
		return $this->lastEditId;
	}

}
