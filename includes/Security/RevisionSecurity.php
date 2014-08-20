<?php

namespace Flow\Security;

class RevisionSecurity {
	protected $user;

	public function __construct( User $user ) {
		$this->user = $user;
		$this->permissions = new Permissions( $user );
	}

	public function sanitize( array $row ) {
		$this->permissions->forRow( $row );
		if ( !$this->permissions->isHistoryAllowed ) {
			return array();
		}

		$row = $this->filterContentFields( $row );
		$row['properties'] = $this->filterProperties( $row );
		$row['links'] = $this->filterLinks( $row );
		$row['actions'] = $this->filterActions( $row );

		// clear out data thats not intended for the end-users
		unset( $row['type'] );

		// @todo post-process should be its own thing
		return $this->postProcess( $row );
	}

	protected function filterContentFields( array $row ) {
		if ( !$this->permissions->isContentAllowed ) {
			unset( $row['content'] );
			$row['size']['new'] = null;
		}

		if ( !$this->permissions->isPreviousContentAllowed ) {
			$row['size']['old'] = null;
		}

		if ( $row['type'] === 'Flow\\Model\\PostRevision' ) {
			if ( !$this->permissions->isSummaryAllowed ) {
				unset( $row['summary'] );
			}
		}

		return $row;
	}

	/**
	 * Adds per-user properties:
	 *
	 * isWatched - the topic is watched by current user
	 * isAlwaysWatched - the topic is always watched by the current user,
	 *   cant unwatch.
	 * watchable - The user could watch this topic, ag anon can't watch a topic
	 *
	 * @param array $row
	 * @return array
	 */
	protected function postProcess( array $row ) {
		$row['isWatched'] = false;
		$row['isAlwaysWatched'] = false;

		// Only non-anon users can watch/unwatch a flow topic
		if ( $this->user->isAnon() ) {
			return $row;
		}

		// @todo would prefer to not do this...
		if ( $this->userTalk === null ) {
			$this->userTalk = $this->user->getTalkPage();
		}
		$title = Title::newFromText( $row['ownerTitle'] );
		if ( $title->isTalkPage() && $title->equals( $this->userTalk ) ) {
			$res['isAlwaysWatched'] = true;
			$res['isWatched'] = true;
		} else {
			$res['watchable'] = true;
			$res['isWatched'] = $this->getWatchStatus( $title );
		}
	}

	protected function filterProperties( array $row, $isContentAllowed, $isHistoryAllowed ) {
		$result = array();
		foreach ( $row['properties'] as $prop => $value ) {
			switch( $prop ) {
			case 'creator-text':
			case 'user-text':
			case 'user-links':
				if ( $this->permissions->isHistoryAllowed ) {
					$result[$prop] = $value;
				}
				break;

			case 'wikitext':
				if ( $this->permissions->isContentAllowed ) {
					$result[$prop] = $value;
				}
				break;

			case 'summary':
				if ( $this->permissions->isSummaryAllowed ) {
					$result[$prop] = $value;
				}
				break;

			case 'prev-wikitext':
				if ( $this->permissions->isPrevContentAllowed ) {
					$result[$prop] = $value;
				} elseif ( !$row['previousRevisionId'] ) {
					$result[$prop] = '';
				}
				break;

			case 'topic-of-post':
				if ( $this->permissions->isTopicTitleAllowed ) {
					$result[$prop] = $value;
				}
				break;

			case 'workflow-url':
			case 'post-url':
			case 'moderated-reason':
				$result[$prop] = $value;
				break;

			// @todo doesn't seem to belong here?
			case 'bundle-count':
				$result[$prop] = $value;
				break;

			case 'post-of-summary':
				// what is this?
			}

			if ( !array_key_exists( $prop, $result ) ) {
				$result[$prop] = null;
			}
		}
	}

	protected function filterLinks( array $row ) {
	}

	protected function filterActions( array $row ) {
	}
}

class Permissions {
	public $isContentAllowed;
	public $isHistoryAllowed;
	public $isSummaryAllowed;
	public $isPreviousContentAllowed;

	public function __construct( User $user ) {
		$this->user = $user;
	}

	public function forRow( array $row ) {
		$this->isContentAllowed = $this->isAllowed( $row, 'view' );
		$this->isHistoryAllowed = $this->isContentAllowed ?: $this->isAllowed( $row, 'history' );
	}
}
