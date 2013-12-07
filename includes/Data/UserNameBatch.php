<?php

namespace Flow\Data;

/**
 * Listen for loaded objects and pre-load their user id fields into
 * the batch loader
 */
class UserNameListener implements LifecycleHandler {
	protected $batch;
	protected $keys;
	protected $wikiKey;
	protected $wiki;

	public function __construct( UserNameBatch $batch, array $keys, $wikiKey = null, $wiki = null ) {
		$this->batch = $batch;
		$this->keys = $keys;
		if ( $wikiKey !== null ) {
			$this->wikiKey = $wikiKey;	
		} elseif ( $wiki === null ) {
			$this->wiki = wfWikiId();
		} else {
			$this->wiki = $wiki;
		}
	}

	public function onAfterLoad( $object, array $row ) {
		if ( $this->wikiKey === null ) {
			$wiki = $this->wiki;
		} else {
			$wiki = $row[$this->wikiKey];
		}
		foreach ( $this->keys as $key ) {
			if ( isset( $row[$key] ) ) {
				$this->batch->add( $wiki, $row[$key] );
			}
		}
	}

	public function onAfterInsert( $object, array $new ) {

	}

	public function onAfterUpdate( $object, array $old, array $new ) {

	}

	public function onAfterRemove( $object, array $old ) {

	}

}

/**
 * Batch together queries for a bunch of wiki+userid -> username
*/
class UserNameBatch {
	/**
	 * @var array map from wikiid to list of userid's to request
	 */
	protected $queued;

	/**
	 * @var array 2-d map from wiki id and user id to display username or false
	 */
	protected $usernames = array();

	/**
	 * @param array $queued map from wikiid to list of userid's to request
	 */
	public function __construct( array $queued = array() ) {
		$this->queued = $queued;
	}

	/**
	 * @param string $wiki
	 * @param integer $userId
	 * @param string $userName Non null to set known usernames like $wgUser
	 */
	public function add( $wiki, $userId, $userName = null ) {
		if ( $userName === null ) {
			$this->queued[$wiki][] = $userId;
		} else {
			$this->usernames[$wiki][$userId] = $userName;
		}
	}

	/**
	 * Get the display username
	 *
	 * @param string $wiki
	 * @param integer $userId
	 * @return string|false Username or false if display is suppressed
	 */
	public function get( $wiki, $userId ) {
		if ( !isset( $this->usernames[$wiki][$userId] ) ) {
			$this->queued[$wiki][] = $userId;
			$this->resolve( $wiki );
		}
		return $this->usernames[$wiki][$userId];
	}

	/**
	 * Resolve all queued user ids to usernames for the given wiki
	 *
	 * @param string $wiki
	 */
	public function resolve( $wiki ) {
		if ( empty( $this->queued[$wiki] ) ) {
			return;
		}
		$queued = array_unique( $this->queued[$wiki] );
		$res = $this->query( $wiki, $queued );
		unset( $this->queued[$wiki] );
		if ( $res ) {
			$found = array();
			foreach ( $res as $row ) {
				$this->usernames[$wiki][$row->user_id] = $row->user_name;
				$found[] = $row->user_id;
			}
			$missing = array_diff( $queued, $found );
		} else {
			$missing = $queued;
		}
		foreach ( $missing as $id ) {
			$this->usernames[$wiki][$id] = false;
		}
	}

	/**
	 * Look up usernames while respecting ipblocks with two queries
	 *
	 * @param string $wiki
	 * @param array $userIds
	 */
	protected function query( $wiki, array $userIds ) {
		$dbr = wfGetDB( DB_SLAVE, array(), $wiki );
		$res = $dbr->select(
			'ipblocks',
			'ipb_user',
			array(
				'ipb_user' => $userIds,
				'ipb_deleted' => 1,
			),
			__METHOD__
		);
		if ( !$res ) {
			return $res;
		}
		$blocked = array();
		foreach ( $res as $row ) {
			$blocked[] = $row->ipb_user;
		}
		// return ids in $userIds that are not in $blocked
		$allowed = array_diff( $userIds, $blocked );
		if ( !$allowed ) {
			return false;
		}
		return $dbr->select(
			'user',
			array( 'user_id', 'user_name' ),
			array( 'user_id' => $allowed ),
			__METHOD__
		);
	}

	/**
	 * Look up usernames while respecting ipblocks with one query.
	 * Unused, check to see if this is reasonable to use.
	 *
	 * @param string $wiki
	 * @param array $userIds
	 */
	protected function executeSingleQuery( $wiki, array $userIds ) {
		$dbr = wfGetDB( DB_SLAVE, array(), $wiki );
		return $dbr->select(
			/* table */ array( 'user', 'ipblocks' ),
			/* select */ array( 'user_id', 'user_name' ),
			/* conds */ array(
				'user_id' => $userIds,
				// only accept records that did not match ipblocks
				'ipb_deleted is null'
			),
			__METHOD__,
			/* options */ array(),
			/* join_conds */ array(
				'ipblocks' => array( 'LEFT OUTER', array(
					'ipb_user' => 'user_id',
					// match only deleted users
					'ipb_deleted' => 1,
				) )
			)
		);
	}
}
