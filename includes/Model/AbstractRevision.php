<?php

namespace Flow\Model;

use Flow\Container;
use Flow\RevisionActionPermissions;
use MWTimestamp;
use User;
use Flow\ParsoidUtils;
use Flow\Exception\DataModelException;
use Flow\Exception\PermissionException;
use Flow\Exception\InvalidInputException;

abstract class AbstractRevision {
	const MODERATED_NONE = '';
	const MODERATED_HIDDEN = 'hide';
	const MODERATED_DELETED = 'delete';
	const MODERATED_SUPPRESSED = 'suppress';

	/**
	 * Metadata relatied to moderation states from least restrictive
	 * to most restrictive.
	 **/
	static public $perms = array(
		self::MODERATED_NONE => array(
			// Whether or not to apply transition to this moderation state to historical revisions
			'historical' => true,
		),
		self::MODERATED_HIDDEN => array(
			// Whether or not to apply transition to this moderation state to historical revisions
			'historical' => false,
		),
		self::MODERATED_DELETED => array(
			// Whether or not to apply transition to this moderation state to historical revisions
			'historical' => true,
		),
		self::MODERATED_SUPPRESSED => array(
			// Whether or not to apply transition to this moderation state to historical revisions
			'historical' => true,
		),
	);

	protected $revId;

	/**
	 * @var integer The user id that created this revision
	 */
	protected $userId;

	/**
	 * @var string|null The ip address of the user that created this revision,
	 *     only when $userId = 0
	 */
	protected $userIp;

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
	// html content has been allowed by the xss check.  When we find the next xss
	// in the parser this hook allows preventing any disply of hostile html. True
	// means the content is allowed. False means not allowed. Null means unchecked
	protected $xssCheck;

	// moderation states for the revision.  This is technically denormalized data
	// since it can be overwritten and does not provide a full history.
	// The tricky part is updating moderation is a new revision for hide and
	// delete, but adjusts an existing revision for full suppression.
	protected $moderationState = self::MODERATED_NONE;
	protected $moderationTimestamp;

	/**
	 * @var integer|null The user id that moderated this revision. Null when
	 *     this revision has never been moderated.
	 */
	protected $moderatedByUserId;

	/**
	 * @var string|null The ip address of the user that moderated this revision,
	 *     only when $moderatedByUserId = 0
	 */
	protected $moderatedByUserIp;

	protected $moderatedReason;

	/**
	 * @var UUID|null The id of the last content edit revision
	 */
	protected $lastEditId;

	/**
	 * @var integer|null The user id that most recently changed the content.
	 */
	protected $lastEditUserId;

	/**
	 * @var string|null The ip address that most recently changed the content,
	 *     only when $lastEditUserId = 0
	 */
	protected $lastEditUserIp;

	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			$obj = new static;
		} elseif ( !$obj instanceof static ) {
			throw new DataModelException( 'wrong object type', 'process-data' );
		}
		$obj->revId = UUID::create( $row['rev_id'] );
		$obj->userId = $row['rev_user_id'];
		if ( isset( $row['rev_user_ip'] ) ) {
			$obj->userIp = $row['rev_user_ip'];
		// BC for rev_user_text field
		} elseif ( isset( $row['rev_user_text'] ) && $obj->userId === 0 ) {
			$obj->userIp = $row['rev_user_text'];
		}
		$obj->prevRevision = UUID::create( $row['rev_parent_id'] );
		$obj->changeType = $row['rev_change_type'];
	 	$obj->flags = array_filter( explode( ',', $row['rev_flags'] ) );
		$obj->content = $row['rev_content'];
		// null if external store is not being used
		$obj->contentUrl = isset( $row['rev_content_url'] ) ? $row['rev_content_url'] : null;
		$obj->decompressedContent = null;

		$obj->moderationState = $row['rev_mod_state'];
		$obj->moderatedByUserId = $row['rev_mod_user_id'];
		if ( isset( $row['rev_mod_user_ip'] ) ) {
			$obj->moderatedByUserIp = $row['rev_mod_user_ip'];
		// BC for rev_mod_user_text field
		} elseif ( isset( $row['rev_mod_user_text'] ) && $obj->moderatedByUserId === 0 ) {
			$obj->moderatedByUserIp = $row['rev_mod_user_text'];
		}
		$obj->moderationTimestamp = $row['rev_mod_timestamp'];
		$obj->moderatedReason = isset( $row['rev_mod_reason'] ) ? $row['rev_mod_reason'] : null;

		// Backwards compatibility
		if ( $obj->moderationState == 'censor' ) {
			$obj->moderationState = self::MODERATED_SUPPRESSED;
		}

		// isset required because there is a possible db migration, cached data will not have it
		$obj->lastEditId = isset( $row['rev_last_edit_id'] ) ? UUID::create( $row['rev_last_edit_id'] ) : null;
		$obj->lastEditUserId = isset( $row['rev_edit_user_id'] ) ? $row['rev_edit_user_id'] : null;
		if ( isset( $row['rev_edit_user_ip'] ) ) {
			$obj->lastEditUserIp = $row['rev_edit_user_ip'];
		// BC for rev_edit_user_text field
		} elseif ( isset( $row['rev_edit_user_text'] ) && $obj->lastEditUserId === 0 ) {
			$obj->lastEditUserIp = $row['rev_edit_user_text'];
		}
		$obj->lastEditUserIp = isset( $row['rev_edit_user_ip'] ) ? $row['rev_edit_user_ip'] : null;

		return $obj;
	}

	static public function toStorageRow( $obj ) {
		return array(
			'rev_id' => $obj->revId->getBinary(),
			'rev_user_id' => $obj->userId,
			'rev_user_ip' => $obj->userIp,
			'rev_parent_id' => $obj->prevRevision ? $obj->prevRevision->getBinary() : null,
			'rev_change_type' => $obj->changeType,
			'rev_type' => $obj->getRevisionType(),

			'rev_content' => $obj->content,
			'rev_content_url' => $obj->contentUrl,
			'rev_flags' => implode( ',', $obj->flags ),

			'rev_mod_state' => $obj->moderationState,
			'rev_mod_user_id' => $obj->moderatedByUserId,
			'rev_mod_user_ip' => $obj->moderatedByUserIp,
			'rev_mod_timestamp' => $obj->moderationTimestamp,
			'rev_mod_reason' => $obj->moderatedReason,

			'rev_last_edit_id' => $obj->lastEditId ? $obj->lastEditId->getBinary() : null,
			'rev_edit_user_id' => $obj->lastEditUserId,
			'rev_edit_user_ip' => $obj->lastEditUserIp,
		);
	}

	/**
	 * NOTE: No guarantee is made here regarding if $this is the newest revision.  Validation
	 * must happen externally.  DB *will* throw an exception if this attempts to write to db
	 * and it is not the most recent revision.
	 */
	public function newNullRevision( User $user ) {
		if ( !$user->isAllowed( 'edit' ) ) {
			throw new PermissionException( 'User does not have core edit permission', 'insufficient-permission' );
		}
		$obj = clone $this;
		$obj->revId = UUID::create();
		list( $obj->userId, $obj->userIp ) = self::userFields( $user );
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

	/**
	 * $historical revisions must be provided when self::needsModerateHistorical
	 * returns true.
	 */
	public function moderate( User $user, $state, $changeType, $reason, array $historical = array() ) {
		if ( ! $this->isValidModerationState( $state ) ) {
			wfWarn( __METHOD__ . ': Provided moderation state does not exist : ' . $state );
			return null;
		}

		// doublecheck if user has permissions for moderation action
		if ( !$this->isAllowed( $user, $changeType ) ) {
			return null;
		}
		if ( !$historical && $this->needsModerateHistorical( $state ) ) {
			throw new InvalidInputException( 'Requested state change requires historical revisions, but they were not provided.', 'invalid-input' );
		}

		$historical[] = $obj = $this->newNullRevision( $user );
		$historical[] = $this;

		$obj->changeType = $changeType;

		$timestamp = wfTimestampNow();
		foreach ( $historical as $rev ) {
			if ( !$rev->isAllowed( $user, $changeType ) ) {
				continue;
			}
			$rev->moderationState = $state;
			list( $userId, $userIp ) = self::userFields( $user );
			if ( $state === self::MODERATED_NONE ) {
				$rev->moderatedByUserId = null;
				$rev->moderatedByUserIp = null;
				$rev->moderationTimestamp = null;
			} else {
				$rev->moderatedByUserId = $userId;
				$rev->moderatedByUserIp = $userIp;
				$rev->moderationTimestamp = $timestamp;
			}
		}

		// This is a bit hacky, but we store the restore reason
		// in the "moderated reason" field. Hmmph.
		$obj->moderatedReason = $reason;

		return $obj;
	}

	public function isValidModerationState( $state ) {
		return isset( self::$perms[$state] );
	}

	public function needsModerateHistorical( $state ) {
		if ( $this->isFirstRevision() ) {
			return false;
		}
		if ( !isset( self::$perms[$state]['historical'] ) ) {
			wfWarn( __METHOD__ . ": Moderation state does not exist : $state" );
			return false;
		}
		return self::$perms[$state]['historical'];
	}

	public function getRevisionId() {
		return $this->revId;
	}

	/**
	 * Is the user allowed to perform a certain action on this revision?
	 *
	 * Uses permissions defined in FlowActions.
	 *
	 * @param User[optional] $user The user requesting access.  When null assumes a user with no permissions.
	 * @param string $action Action to check if allowed.
	 * @return boolean True when the user is allowed to see the current revision
	 */
	protected function isAllowed( $user = null, $action ) {
		// if no user specified, assume anonymous user
		if ( !$user instanceof User ) {
			$user = new User;
		}

		$actions = Container::get( 'flow_actions' );
		$permissions = new RevisionActionPermissions( $actions, $user );

		return $permissions->isAllowed( $this, $action );
	}

	public function hasHiddenContent() {
		return $this->moderationState === self::MODERATED_HIDDEN;
	}

	public function getContentRaw() {
		if ( $this->decompressedContent === null ) {
			$this->decompressedContent = \Revision::decompressRevisionText( $this->content, $this->flags );
		}

		return $this->decompressedContent;
	}

	/**
	 * DO NOT USE THIS METHOD to output the content; use
	 * Templating::getContent, which will do additional (permissions-based)
	 * checks to make sure it outputs something the user can see.
	 *
	 * @param string[optional] $format Format to output content in (html|wikitext)
	 * @return string
	 */
	public function getContent( $format = 'html' ) {
		if ( $this->xssCheck === false ) {
			return '';
		}
		$raw = $this->getContentRaw();
		$sourceFormat = $this->getContentFormat();
		if ( $this->xssCheck === null && $sourceFormat === 'html' ) {
			// returns true if no handler aborted the hook
			$this->xssCheck = wfRunHooks( 'FlowCheckHtmlContentXss', array( $raw ) );
			if ( !$this->xssCheck ) {
				wfDebugLog( __CLASS__, __FUNCTION__ . ': XSS check prevented display of revision ' . $this->revId->getHex() );
				return '';
			}
		}

		if ( !$this->isFormatted() ) {
			return $raw;
		}
		if ( !isset( $this->convertedContent[$format] ) ) {
			$this->convertedContent[$format] = ParsoidUtils::convert( $sourceFormat, $format, $raw );
		}

		return $this->convertedContent[$format];
	}

	/**
	 * @return integer
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * @return string|null
	 */
	public function getUserIp() {
		return $this->userIp;
	}

	/**
	 * Should only be used for setting the initial content.  To set subsequent content
	 * use self::setNextContent
	 *
	 * @param string $content Content in wikitext format
	 * @throws \MWException
	 */
	protected function setContent( $content ) {
		if ( $this->moderationState !== self::MODERATED_NONE ) {
			throw new DataModelException( 'TODO: Cannot change content of restricted revision', 'process-data' );
		}

		// TODO: How is this guarantee of only receiving wikitext made?
		$inputFormat = 'wikitext';
		if ( $this->content !== null ) {
			throw new DataModelException( 'Updating content must use setNextContent method', 'process-data' );
		}
		// Keep consistent with normal edit page, trim only trailing whitespaces
		$content = rtrim( $content );
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
			throw new DataModelException( 'Cannot change content of restricted revision', 'process-data' );
		}
		if ( $content !== $this->getContent() ) {
			$this->content = null;
			$this->setContent( $content );
			$this->lastEditId = $this->getRevisionId();
			list( $this->lastEditUserId, $this->lastEditUserIp ) = self::userFields( $user );
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
	 * @return string The content format of this revision
	 */
	public function getContentFormat() {
		return in_array( 'html', $this->flags ) ? 'html' : 'wikitext';
	}

	/**
	 * Determines the appropriate format to store content in.
	 * Usually, the default storage format, but if isFormatted() returns
	 * false, then it will return 'wikitext'.
	 * NOTE: The format of the current content is retrieved with getContentFormat
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

	public function getModeratedReason() {
		return $this->moderatedReason;
	}

	public function isModerated() {
		return $this->moderationState !== self::MODERATED_NONE;
	}

	public function isHidden() {
		return $this->moderationState === self::MODERATED_HIDDEN;
	}

	public function isSuppressed() {
		return $this->moderationState === self::MODERATED_SUPPRESSED;
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

	public function getLastContentEditId() {
		return $this->lastEditId;
	}

	public function getModeratedByUserId() {
		return $this->moderatedByUserId;
	}

	public function getModeratedByUserIp() {
		return $this->moderatedByUserIp;
	}

	/**
	 * Return a (userId, userIp) tuple for the given
	 * user object.  userIp is null for userId != 0
	 */
	static public function userFields( $user ) {
		if ( $user->isAnon() ) {
			$userId = 0;
			$userIp = $user->getName();
		} else {
			$userId = $user->getId();
			$userIp = null;
		}
		return array( $userId, $userIp );
	}

	abstract public function getRevisionType();
}
