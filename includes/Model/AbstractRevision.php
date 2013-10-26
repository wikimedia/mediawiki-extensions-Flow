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

	/**
	 * Metadata relatied to moderation states from least restrictive
	 * to most restrictive.
	 **/
	static protected $perms = array(
		self::MODERATED_NONE => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => null,
			// i18n key to replace content with when state is active(unused with perm === null )
			'content' => null,
			// This is the bit of text rendered instead of the post creator
			'usertext' => null,
			// Whether or not to create a new revision when setting this state
			'new-revision' => true,
			// i18n key for history and recentchanges
			'change-type' => 'flow-rev-message-restored-post',
		),
		self::MODERATED_HIDDEN => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => 'flow-hide',
			// i18n key to replace content with when state is active
			// NOTE: special case self::getHiddenContent still retrieves content in this case only
			'content' => 'flow-post-hidden-by',
			// This is the bit of text rendered instead of the post creator
			'usertext' => 'flow-rev-message-hid-post',
			// Whether or not to create a new revision when setting this state
			'new-revision' => true,
			// i18n key for history and recentchanges
			'change-type' => 'flow-rev-message-hid-post',
		),
		self::MODERATED_DELETED => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => 'flow-delete',
			// i18n key to replace content with when state is active
			'content' => 'flow-post-deleted-by',
			// This is the bit of text rendered instead of the post creator
			'usertext' => 'flow-rev-message-deleted-post',
			// Whether or not to create a new revision when setting this state
			'new-revision' => false,
			// i18n key for history and recentchanges
			'change-type' => 'flow-rev-message-deleted-post',
		),
		self::MODERATED_CENSORED => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => 'flow-censor',
			// i18n key to replace content with when state is active
			'content' => 'flow-post-censored-by',
			// This is the bit of text rendered instead of the post creator
			'usertext' => 'flow-rev-message-censored-post',
			// Whether or not to create a new revision when setting this state
			'new-revision' => false,
			// i18n key for history and recentchanges
			'change-type' => 'flow-rev-message-censored-post',
		),
	);

	protected $revId;
	protected $userId;
	protected $userText;

	/**
	 * Array of flags strictly related to the content. Flags are reset when
	 * content changes.
	 */
	protected $flags = array();

	// An i18n message key indicating what kind of change this revision is
	// primary use case is the a revision history list.
	// TODO: i18n key may be too limiting, consider allowing custom revision comments
	protected $changeType;
	// UUID of the revision prior to this one, or null if this is first revision
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
		$obj->changeType = $row['rev_change_type'];
	 	$obj->flags = array_filter( explode( ',', $row['rev_flags'] ) );
		$obj->content = $row['rev_content'];
		// null if external store is not being used
		$obj->contentUrl = isset( $row['rev_content_url'] ) ? $row['rev_content_url'] : null;
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
			'rev_change_type' => $obj->changeType,
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
		$obj->changeType = '';
		return $obj;
	}

	/**
	 * Create the next revision with new content
	 */
	public function newNextRevision( User $user, $content, $changeType ) {
		$obj = $this->newNullRevision( $user );
		$obj->setNextContent( $user, $content );
		$obj->changeType = $changeType;
		return $obj;
	}

	protected function mostRestrictivePermission( $a, $b ) {
		$keys = array_keys( self::$perms );
		$aPos = array_search( $a, $keys );
		$bPos = array_search( $b, $keys );
		if ( $aPos === false || $bPos === false ) {
			wfWarn( __CLASS__, __FUNCTION__ . ": Invalid permissions provided: '$a' '$b'" );
			// err on the side of safety, most restrictive
			return end( $keys );
		}
		return $keys[max( $aPos, $bPos )];
	}

	public function moderate( User $user, $state, $changeType = null ) {
		if ( ! $this->isValidModerationState( $state ) ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Provided moderation state does not exist : ' . $state );
			return null;
		}

		$mostRestrictive = self::mostRestrictivePermission( $state, $this->moderationState );
		if ( !$this->isAllowed( $user, $mostRestrictive ) ) {
			return null;
		}
		// Censoring is special,  other moderation types just create
		// a new revision but censoring adjusts the existing revision.
		// Yes this mucks with the history just being a revision list.
		if ( self::$perms[$state]['new-revision'] ) {
			$obj = $this->newNullRevision( $user );
		} else {
			$obj = $this;
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

		if ( $obj !== $this ) {
			if ( $changeType === null && isset( self::$perms[$state]['change-type'] ) ) {
				$obj->changeType = self::$perms[$state]['change-type'];
			} else {
				$obj->changeType = $changeType;
			}
		}

		return $obj;
	}

	public function isValidModerationState( $state ) {
		return isset( self::$perms[$state] );
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
	public function isAllowed( $user = null, $state = null ) {
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

	public function hasHiddenContent() {
		return $this->moderationState === self::MODERATED_HIDDEN;
	}

	public function getHiddenContent( $format ) {
		if ( $this->hasHiddenContent() ) {
			return $this->getConvertedContent( $format );
		}
		return '';
	}

	public function getContent( $user = null, $format = 'html' ) {
		if ( $this->isAllowed( $user ) ) {
			return $this->getConvertedContent( $format );
		} else {
			$moderatedAt = new MWTimestamp( $this->moderationTimestamp );

			// Messages: flow-post-hidden-by, flow-post-deleted-by, flow-post-censored-by
			return wfMessage(
				self::$perms[$this->moderationState]['content'],
				$this->moderatedByUserText,
				$moderatedAt->getHumanTimestamp()
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
		// The text of *this* revision is only stripped when fully moderated
		if ( $this->isCensored() ) {
			// Messages: flow-post-hidden, flow-post-deleted, flow-post-censored
			return wfMessage( self::$perms[$this->moderationState]['usertext'] );
		} else {
			return $this->getUserTextRaw();
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
		if ( $this->moderationState !== self::MODERATED_NONE ) {
			throw new \Exception( 'TODO: Cannot change content of restricted revision' );
		}

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

	public function getChangeType() {
		return $this->changeType;
	}

	public function getModerationState() {
		return $this->moderationState;
	}

	public function isModerated() {
		return $this->moderationState !== self::MODERATED_NONE;
	}

	public function isHidden() {
		return $this->moderationState === self::MODERATED_HIDDEN;
	}

	public function isCensored() {
		return $this->moderationState === self::MODERATED_CENSORED;
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

	public function getModeratedByUserText() {
		return $this->moderatedByUserText;
	}

	public function getModeratedByUserId() {
		return $this->moderatedByUserId;
	}
}
