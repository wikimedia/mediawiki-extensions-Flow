<?php

namespace Flow\Model;

use Flow\Collection\AbstractCollection;
use Flow\Container;
use Flow\Exception\DataModelException;
use Flow\Exception\PermissionException;
use Flow\Parsoid\Utils;
use Flow\RevisionActionPermissions;
use Title;
use User;

abstract class AbstractRevision {
	const MODERATED_NONE = '';
	const MODERATED_HIDDEN = 'hide';
	const MODERATED_DELETED = 'delete';
	const MODERATED_SUPPRESSED = 'suppress';
	const MODERATED_CLOSED = 'close';

	/**
	 * List of available permission levels.
	 *
	 * @var string[]
	 **/
	static public $perms = array(
		self::MODERATED_NONE,
		self::MODERATED_HIDDEN,
		self::MODERATED_DELETED,
		self::MODERATED_SUPPRESSED,
		self::MODERATED_CLOSED
	);

	/**
	 * @var UUID
	 */
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
	 * @var string|null The wiki of the user that created this revision
	 */
	protected $userWiki;

	/**
	 * Array of flags strictly related to the content. Flags are reset when
	 * content changes.
	 *
	 * @var string[]
	 */
	protected $flags = array();

	/**
	 * Name of the action performed that generated this revision.
	 *
	 * @see FlowActions.php
	 * @var string
	 */
	protected $changeType;

	/**
	 * @var UUID|null The id of the revision prior to this one, or null if this is first revision
	 */
	protected $prevRevision;

	/**
	 * @var string Raw content of revision
	 */
	protected $content;

	/**
	 * @var string|null Only populated when external store is in use
	 */
	protected $contentUrl;

	/**
	 * @var string|null This is decompressed on-demand from $this->content in self::getContent()
	 */
	protected $decompressedContent;

	/**
	 * @var string[] Converted (wikitext|html) content, based off of $this->decompressedContent
	 */
	protected $convertedContent = array();

	/**
	 * html content has been allowed by the xss check.  When we find the next xss
	 * in the parser this hook allows preventing any display of hostile html. True
	 * means the content is allowed. False means not allowed. Null means unchecked
	 *
	 * @var boolean
	 */
	protected $xssCheck;

	/**
	 * moderation states for the revision.  This is technically denormalized data
	 * since it can be overwritten and does not provide a full history.
	 * The tricky part is updating moderation is a new revision for hide and
	 * delete, but adjusts an existing revision for full suppression.
	 *
	 * @var string
	 */
	protected $moderationState = self::MODERATED_NONE;

	/**
	 * @var string|null
	 */
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

	/**
	 * @var string|null The wiki of the user that moderated this revision
	 */
	protected $moderatedByUserWiki;

	/**
	 * @var string|null
	 */
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

	/**
	 * @var string|null The wiki of the user that most recently changed the content
	 */
	protected $lastEditUserWiki;

	/**
	 * @var RevisionState[]
	 */
	protected $revisionState = array();

	/**
	 * @param string[] $row
	 * @param AbstractRevision|null $obj
	 * @return AbstractRevision
	 * @throws DataModelException
	 */
	static public function fromStorageRow( array $row, $obj = null ) {
		if ( $obj === null ) {
			/** @var AbstractRevision $obj */
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
		$obj->userWiki = isset( $row['rev_user_wiki'] ) ? $row['rev_user_wiki'] : '';
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
		$obj->moderatedByUserWiki = isset( $row['rev_mod_user_wiki'] ) ? $row['rev_mod_user_wiki'] : null;
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
		$obj->lastEditUserWiki = isset( $row['rev_edit_user_wiki'] ) ? $row['rev_edit_user_wiki'] : null;

		// Revision state
		if ( isset( $row['frs'] ) ) {
			foreach ( unserialize( $row['frs'] ) as $state ) {
				$this->revisionState[$state->getState()] = RevisionState::fromStorageRow( $state );
			}
		}

		return $obj;
	}

	/**
	 * @param AbstractRevision $obj
	 * @return string[]
	 */
	static public function toStorageRow( $obj ) {
		$state = array();
		foreach ( $obj->revisionState as $state ) {
			$state[$state->getState()] = serialize( array(
				// Use getter method because PHP doesn't have package visibility
				'frs_rev_id' => $state->getRevId()->getAlphadecimal(),
				'frs_state' => $state->getState(),
				'frs_user_id' => $state->getUserId(),
				'frs_user_ip' => $state->getUserIp(),
				'frs_user_wiki' => $state->getUserWiki(),
				'frs_comment' => $state->getComment()
			) );
		}
		return array(
			'rev_id' => $obj->revId->getAlphadecimal(),
			'rev_user_id' => $obj->userId,
			'rev_user_ip' => $obj->userIp,
			'rev_user_wiki' => $obj->userWiki,
			'rev_parent_id' => $obj->prevRevision ? $obj->prevRevision->getAlphadecimal() : null,
			'rev_change_type' => $obj->changeType,
			'rev_type' => $obj->getRevisionType(),
			'rev_type_id' => $obj->getCollectionId()->getAlphadecimal(),

			'rev_content' => $obj->content,
			'rev_content_url' => $obj->contentUrl,
			'rev_flags' => implode( ',', $obj->flags ),

			'rev_mod_state' => $obj->moderationState,
			'rev_mod_user_id' => $obj->moderatedByUserId,
			'rev_mod_user_ip' => $obj->moderatedByUserIp,
			'rev_mod_user_wiki' => $obj->moderatedByUserWiki,
			'rev_mod_timestamp' => $obj->moderationTimestamp,
			'rev_mod_reason' => $obj->moderatedReason,

			'rev_last_edit_id' => $obj->lastEditId ? $obj->lastEditId->getAlphadecimal() : null,
			'rev_edit_user_id' => $obj->lastEditUserId,
			'rev_edit_user_ip' => $obj->lastEditUserIp,
			'rev_edit_user_wiki' => $obj->lastEditUserWiki,
			'frs' => $state
		);
	}

	/**
	 * NOTE: No guarantee is made here regarding if $this is the newest revision.  Validation
	 * must happen externally.  DB *will* throw an exception if this attempts to write to db
	 * and it is not the most recent revision.
	 *
	 * @param User $user
	 * @return AbstractRevision
	 * @throws PermissionException
	 */
	public function newNullRevision( User $user ) {
		if ( !$user->isAllowed( 'edit' ) ) {
			throw new PermissionException( 'User does not have core edit permission', 'insufficient-permission' );
		}
		$obj = clone $this;
		$obj->revId = UUID::create();
		list( $obj->userId, $obj->userIp, $obj->userWiki ) = self::userFields( $user );
		$obj->prevRevision = $this->revId;
		$obj->changeType = '';
		return $obj;
	}

	/**
	 * Create the next revision with new content
	 *
	 * @param User $user
	 * @param string $content
	 * @param string $changeType
	 * @return AbstractRevision
	 */
	public function newNextRevision( User $user, $content, $changeType ) {
		$obj = $this->newNullRevision( $user );
		$obj->setNextContent( $user, $content );
		$obj->changeType = $changeType;
		return $obj;
	}

	/**
	 * @param User $user
	 * @param string $state
	 * @param string $changeType
	 * @param string $reason
	 * @return AbstractRevision
	 */
	public function moderate( User $user, $state, $changeType, $reason ) {
		if ( ! $this->isValidModerationState( $state ) ) {
			wfWarn( __METHOD__ . ': Provided moderation state does not exist : ' . $state );
			return null;
		}

		// double check if user has permissions for moderation action
		if ( !$this->isAllowed( $user, $changeType ) ) {
			return null;
		}

		$obj = $this->newNullRevision( $user );
		$obj->changeType = $changeType;

		// This is a bit hacky, but we store the restore reason
		// in the "moderated reason" field. Hmmph.
		$obj->moderatedReason = $reason;
		$obj->moderationState = $state;

		if ( $state === self::MODERATED_NONE ) {
			$obj->moderatedByUserId = null;
			$obj->moderatedByUserIp = null;
			$obj->moderatedByUserWiki = null;
			$obj->moderationTimestamp = null;
		} else {
			list( $userId, $userIp, $userWiki ) = self::userFields( $user );
			$obj->moderatedByUserId = $userId;
			$obj->moderatedByUserIp = $userIp;
			$obj->moderatedByUserWiki = $userWiki;
			$obj->moderationTimestamp = wfTimestampNow();
		}

		return $obj;
	}

	/**
	 * @param string $state
	 * @return boolean
	 */
	public function isValidModerationState( $state ) {
		return in_array( $state, self::$perms );
	}

	/**
	 * @return UUID
	 */
	public function getRevisionId() {
		return $this->revId;
	}

	/**
	 * Is the user allowed to perform a certain action on this revision?
	 *
	 * Uses permissions defined in FlowActions.
	 *
	 * @param User|null $user The user requesting access.  When null assumes a user with no permissions.
	 * @param string $action Action to check if allowed.
	 * @return boolean True when the user is allowed to see the current revision
	 */
	protected function isAllowed( User $user = null, $action ) {
		// if no user specified, assume anonymous user
		if ( !$user instanceof User ) {
			$user = new User;
		}

		$actions = Container::get( 'flow_actions' );
		$permissions = new RevisionActionPermissions( $actions, $user );

		return $permissions->isAllowed( $this, $action );
	}

	/**
	 * @return boolean
	 */
	public function hasHiddenContent() {
		return $this->moderationState === self::MODERATED_HIDDEN;
	}

	/**
	 * @return string
	 */
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
				wfDebugLog( 'Flow', __METHOD__ . ': XSS check prevented display of revision ' . $this->revId->getAlphadecimal() );
				return '';
			}
		}

		if ( !$this->isFormatted() ) {
			return $raw;
		}
		if ( !isset( $this->convertedContent[$format] ) ) {
			if ( $sourceFormat === $format ) {
				$this->convertedContent[$format] = $raw;
			} else {
				$this->convertedContent[$format] = Utils::convert(
					$sourceFormat,
					$format,
					$raw,
					$this->getCollection()->getTitle()
				);
			}
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

	public function getUserWiki() {
		return $this->userWiki;
	}

	/**
	 * Should only be used for setting the initial content.  To set subsequent content
	 * use self::setNextContent
	 *
	 * @param string $content Content in wikitext format
	 * @param Title|null $title When null the related workflow will be lazy-loaded to locate the title
	 * @throws DataModelException
	 */
	protected function setContent( $content, Title $title = null ) {
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
			$this->convertedContent[$storageFormat] = Utils::convert(
				$inputFormat,
				$storageFormat,
				$content,
				$title ?: $this->getCollection()->getTitle()
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
	 *
	 * @param User $user
	 * @param string $content
	 * @throws DataModelException
	 */
	protected function setNextContent( User $user, $content ) {
		if ( $this->moderationState !== self::MODERATED_NONE ) {
			throw new DataModelException( 'Cannot change content of restricted revision', 'process-data' );
		}
		if ( $content !== $this->getContent() ) {
			$this->content = null;
			$this->setContent( $content );
			$this->lastEditId = $this->getRevisionId();
			list( $this->lastEditUserId, $this->lastEditUserIp, $this->lastEditUserWiki ) = self::userFields( $user );
		}
	}

	/**
	 * Determines whether this revision contains formatted content
	 * (i.e. content with separate HTML and WikiText representations)
	 * or unformatted content (i.e. one plaintext representation)
	 * Note that this function may return different values for different
	 * instances of the same class.
	 *
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
	 *
	 * @return string The name of the storage format.
	 */
	protected function getStorageFormat() {
		global $wgFlowContentFormat;
		return $this->isFormatted() ? $wgFlowContentFormat : 'wikitext';
	}

	/**
	 * @return UUID|null
	 */
	public function getPrevRevisionId() {
		return $this->prevRevision;
	}

	/**
	 * @return string
	 */
	public function getChangeType() {
		return $this->changeType;
	}

	/**
	 * @param string
	 */
	public function setChangeType( $changeType ) {
		$this->changeType = $changeType;
	}

	/**
	 * @return string
	 */
	public function getModerationState() {
		return $this->moderationState;
	}

	/**
	 * @return revisionState[]
	 */
	public function getRevisionState() {
		return $this->revisionState;
	}

	/**
	 * @param RevisionState[]
	 */
	public function setRevisionState( array $revisionState ) {
		$this->revisionState = $revisionState;
	}

	/**
	 * @return string|null
	 */
	public function getModeratedReason() {
		return $this->moderatedReason;
	}

	/**
	 * @return boolean
	 */
	public function isModerated() {
		return $this->moderationState !== self::MODERATED_NONE;
	}

	/**
	 * @return boolean
	 */
	public function isHidden() {
		return $this->moderationState === self::MODERATED_HIDDEN;
	}

	/**
	 * @return boolean
	 */
	public function isSuppressed() {
		return $this->moderationState === self::MODERATED_SUPPRESSED;
	}

	/**
	 * @return boolean
	 */
	public function isClosed() {
		return $this->moderationState === self::MODERATED_CLOSED;
	}

	/**
	 * @return string|null Timestamp in TS_MW format
	 */
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

	/**
	 * @param string|array $flags
	 * @return boolean
	 */
	public function isFlaggedAll( $flags ) {
		foreach ( (array) $flags as $flag ) {
			if ( false === array_search( $flag, $this->flags ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @return boolean
	 */
	public function isFirstRevision() {
		return $this->prevRevision === null;
	}

	/**
	 * @return boolean
	 */
	public function isOriginalContent() {
		return $this->lastEditId === null;
	}

	/**
	 * @return UUID
	 */
	public function getLastContentEditId() {
		return $this->lastEditId;
	}

	/**
	 * @return integer
	 */
	public function getLastContentEditUserId() {
		return $this->lastEditUserId;
	}

	/**
	 * @return string|null
	 */
	public function getLastContentEditUserIp() {
		return $this->lastEditUserIp;
	}

	/**
	 * @return string|null
	 */
	public function getLastContentEditUserWiki() {
		return $this->lastEditUserWiki;
	}

	/**
	 * @return integer|null
	 */
	public function getModeratedByUserId() {
		return $this->moderatedByUserId;
	}

	/**
	 * @return string|null
	 */
	public function getModeratedByUserIp() {
		return $this->moderatedByUserIp;
	}

	public function getModeratedByUserWiki() {
		return $this->moderatedByUserWiki;
	}

	/**
	 * Return a (userId, userIp, wikiId) tuple for the given
	 * user object.  userIp is null for userId != 0
	 *
	 * @param User $user
	 * @return array
	 */
	static public function userFields( $user ) {
		if ( $user->isAnon() ) {
			$userId = 0;
			$userIp = $user->getName();
		} else {
			$userId = $user->getId();
			$userIp = null;
		}
		return array( $userId, $userIp, wfWikiId() );
	}

	/**
	 * @return string
	 */
	abstract public function getRevisionType();

	/**
	 * @return UUID
	 */
	abstract public function getCollectionId();

	/**
	 * @return AbstractCollection
	 */
	abstract public function getCollection();
}
