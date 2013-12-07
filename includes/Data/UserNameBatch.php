<?php
/**
 * Provide usernames filtered by per-wiki ipblocks. Batches together
 * database requests for multiple usernames when possible.
 */
namespace Flow\Data;

/**
 * Listen for loaded objects and pre-load their user id fields into
 * a batch username loader.
 */
class UserNameListener implements LifecycleHandler {
	protected $batch;
	protected $keys;
	protected $wikiKey;
	protected $wiki;

	/**
	 * @param UserNameBatch $batch
	 * @param array $keys A list of keys from storage that contain user ids
	 * @param string|null $wikiKey A key from the storage row that contains the wiki id.
	 * @param string|null $wiki The wikiid to use when $wikiKey is null. If both are null wfWikiId() is used
	 */
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

	/**
	 * Load any user ids in $row into the username batch
	 */
	public function onAfterLoad( $object, array $row ) {
		if ( $this->wikiKey === null ) {
			$wiki = $this->wiki;
		} elseif( isset( $row[$this->wikiKey] ) ) {
			$wiki = $row[$this->wikiKey];
		} else {
			wfDebugLog( __CLASS__, __METHOD__ . ": could not detect wiki with {$this->wikiKey}" );
			return;
		}
		foreach ( $this->keys as $key ) {
			if ( isset( $row[$key] ) && $row[$key] != 0 ) {
				$this->batch->add( $wiki, $row[$key] );
			}
		}
	}

	public function onAfterInsert( $object, array $new ) {}
	public function onAfterUpdate( $object, array $old, array $new ) {}
	public function onAfterRemove( $object, array $old ) {}
}

/**
 * Batch together queries for a bunch of wiki+userid -> username
 */
class UserNameBatch {
	/**
	 * @var array map from wikiid to list of userid's to request
	 */
	protected $queued = array();

	/**
	 * @var array 2-d map from wiki id and user id to display username or false
	 */
	protected $usernames = array();

	/**
	 * @param array $queued map from wikiid to list of userid's to request
	 */
	public function __construct( array $queued = array() ) {
		foreach ( $queued as $wiki => $userIds ) {
			$this->queued[$wiki] = array_map( 'intval', $userIds );
		}
	}

	/**
	 * @param string $wiki
	 * @param integer $userId
	 * @param string $userName Non null to set known usernames like $wgUser
	 */
	public function add( $wiki, $userId, $userName = null ) {
		$userId = (int)$userId;
		if ( $userName !== null ) {
			$this->usernames[$wiki][$userId] = $userName;
		} elseif ( !isset( $this->usernames[$wiki][$userId] ) ) {
			$this->queued[$wiki][] = $userId;
		}
	}

	/**
	 * Get the displayable username
	 *
	 * @param string $wiki
	 * @param integer $userId
	 * @param string|false $userIp
	 * @return string|false false if username is not found or display is suppressed
	 * @todo Return something better for not found / suppressed, but what? Making
	 *   return type string|Message would suck.
	 */
	public function get( $wiki, $userId, $userIp = false ) {
		$userId = (int)$userId;
		if ( $userId === 0 ) {
			return $userIp;
		}
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
		if ( isset( $this->usernames[$wiki] ) ) {
			$queued = array_diff( $queued, array_keys( $this->usernames[$wiki] ) );
		}
		$res = $this->query( $wiki, $queued );
		unset( $this->queued[$wiki] );
		if ( $res ) {
			$found = array();
			foreach ( $res as $row ) {
				$id = (int)$row->user_id;
				$this->usernames[$wiki][$id] = $row->user_name;
				$found[] = $id;
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
	protected function querySingle( $wiki, array $userIds ) {
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
