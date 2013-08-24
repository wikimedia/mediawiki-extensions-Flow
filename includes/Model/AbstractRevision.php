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
			// i18n key for history and recentchanges comment
			'comment' => 'flow-comment-restored',
		),
		self::MODERATED_HIDDEN => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => 'flow-hide',
			// i18n key to replace content with when state is active
			// NOTE: special case self::getHiddenContent still retrieves content in this case only
			'content' => 'flow-post-hidden-by',
			// i18n key for history and recentchanges comment
			'comment' => 'flow-comment-hidden',
		),
		self::MODERATED_DELETED => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => 'flow-delete',
			// i18n key to replace content with when state is active
			'content' => 'flow-post-deleted-by',
			// i18n key for history and recentchanges comment
			'comment' => 'flow-comment-deleted',
		),
		self::MODERATED_CENSORED => array(
			// The permission needed from User::isAllowed to see and create new revisions
			'perm' => 'flow-censor',
			// i18n key to replace content with when state is active
			'content' => 'flow-post-censored-by',
			// i18n key for history and recentchanges comment
			'comment' => 'flow-comment-censored',
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
	protected $comment;
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
		$obj->comment = '';
		return $obj;
	}

	public function newNextRevision( User $user, $content, $comment ) {
		$obj = $this->newNullRevision( $user );
		$obj->setContent( $content );
		$obj->comment = $comment;
		return $obj;
	}

	protected function mostRestrictivePermission( $a, $b ) {
		$keys = array_keys( self::$perms );
		$aPos = array_search( $a, $keys );
		$bPos = array_search( $b, $keys );
		if ( $aPos === false || $bPos === false ) {
			wfWarn( __CLASS__, __FUNCTION__ . ": Invalid permissions provided: '$a' '$b'" );
			// err on the side of safety, most restrictive
			return end( self::$perms );
		}
		return self::$perms[max( $aPos, $bPos )];
	}

	public function moderate( User $user, $state, $comment = null ) {
		if ( !isset( self::$perms[$state] ) ) {
			wfDebugLog( __CLASS__, __FUNCTION__ . ': Provided moderation state does not exist : ' . $state );
			return null;
		}

		$permission = self::mostRestrictivePermission( $state, $this->moderationState );
		$mostRestricted = $keys[max( $oldPos, $newPos )];
		if ( !$this->isAllowed( $user, $mostRestricted ) ) {
			return null;
		}
		// Censoring is special,  other moderation types just create
		// a new revision but censoring adjusts the existing revision.
		// Yes this mucks with the history just being a revision list.
		if ( in_array( $state, array( self::MODERATED_CENSORED, self::MODERATED_DELETED ) ) ) {
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
		if ( $comment === null && isset( self::$perms[$state]['comment'] ) ) {
			$obj->comment = self::$perms[$state]['comment'];
		} else {
			$obj->comment = $comment;
		}
		return $obj;
	}

	public function restore( User $user ) {
		return $this->moderate( $user, self::MODERATED_NONE );
	}

	public function getRevisionId() {
		return $this->revId;
	}

	// Is the user allowed to see this revision ?
	protected function isAllowed( $user = null, $state = null ) {
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

	public function getUserText( $user = null ) {
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
	 * @param string $content Content in wikitext format
	 * @throws \Exception
	 */
	protected function setContent( $content ) {
		if ( $this->moderationState !== self::MODERATED_NONE ) {
			throw new \Exception( 'TODO: Cannot change content of restricted revision' );
		}

		if ( $content !== $this->getContent( null, 'wikitext') ) {
			$this->convertedContent['wikitext'] = $content;

			// convert content to desired storage format
			global $wgFlowContentFormat;
			if ( !isset( $this->convertedContent[$wgFlowContentFormat] ) ) {
				$this->convertedContent[$wgFlowContentFormat] =
					ParsoidUtils::convert(
						'wikitext',
						$wgFlowContentFormat,
						$this->convertedContent['wikitext']
					);
			}

			$this->content = $this->decompressedContent = $this->convertedContent[$wgFlowContentFormat];
			$this->contentUrl = null;

			// flags are strictly related to the content
			// should this only remove a subset of flags?
			$this->flags = array_filter( explode( ',', \Revision::compressRevisionText( $this->content ) ) );
			$this->flags[] = $wgFlowContentFormat;
		}
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
}
