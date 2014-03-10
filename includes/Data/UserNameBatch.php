<?php
/**
 * Provide usernames filtered by per-wiki ipblocks. Batches together
 * database requests for multiple usernames when possible.
 */
namespace Flow\Data;

use Flow\DbFactory;
use User;

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
	 * @param array $keys key - a list of keys from storage that contain user ids, value - the wiki for the user id lookup, default to $wiki if null
	 * @param string|null $wiki The wikiid to use when $wikiKey is null. If both are null wfWikiId() is used
	 */
	public function __construct( UserNameBatch $batch, array $keys, $wiki = null ) {
		$this->batch = $batch;
		$this->keys = $keys;

		if ( $wiki === null ) {
			$this->wiki = wfWikiId();
		} else {
			$this->wiki = $wiki;
		}
	}

	/**
	 * Load any user ids in $row into the username batch
	 */
	public function onAfterLoad( $object, array $row ) {
		foreach ( $this->keys as $userKey => $wikiKey ) {
			// check if the user id key exists in the data array and
			// make sure it has a non-zero value
			if ( isset( $row[$userKey] ) && $row[$userKey] != 0 ) {
				// the wiki for the user id lookup is specified,
				// check if it exists in the data array
				if ( $wikiKey ) {
					if ( !isset( $row[$wikiKey] ) ) {
						wfDebugLog( 'Flow', __METHOD__ . ": could not detect wiki with " . $wikiKey );
						continue;
					}
					$wiki = $row[$wikiKey];
				// no wiki lookup is specified, default to $this->wiki
				} else {
					$wiki = $this->wiki;
				}
				$this->batch->add( $wiki, $row[$userKey] );
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
	 * @var array[] map from wikiid to list of userid's to request
	 */
	protected $queued = array();

	/**
	 * @var array[] 2-d map from wiki id and user id to display username or false
	 */
	protected $usernames = array();

	/**
	 * @param UserNameQuery $query
	 * @param array $queued map from wikiid to list of userid's to request
	 */
	public function __construct( UserNameQuery $query, array $queued = array() ) {
		$this->query = $query;
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
	 * @param string|boolean $userIp
	 * @return string|boolean false if username is not found or display is suppressed
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
		$res = $this->query->execute( $wiki, $queued );
		unset( $this->queued[$wiki] );
		if ( $res ) {
			$usernames = array();
			foreach ( $res as $row ) {
				$id = (int)$row->user_id;
				$this->usernames[$wiki][$id] = $usernames[$id] = $row->user_name;
			}
			$this->resolveUserPages( $wiki, $usernames );
			$missing = array_diff( $queued, array_keys( $usernames ) );
		} else {
			$missing = $queued;
		}
		foreach ( $missing as $id ) {
			$this->usernames[$wiki][$id] = false;
		}
	}

	/**
	 * Update in-process title existence cache with NS_USER and
	 * NS_USER_TALK pages related to the provided usernames.
	 *
	 * @param string $wiki Wiki the users belong to
	 * @param array $usernames List of user names
	 */
	protected function resolveUserPages( $wiki, array $usernames ) {
		// LinkBatch currently only supports the current wiki
		if ( $wiki !== wfWikiId() || !$usernames ) {
			return;
		}

		$lb = new \LinkBatch();
		foreach ( $usernames as $name ) {
			$user = User::newFromName( $name );
			if ( $user ) {
				$lb->addObj( $user->getUserPage() );
				$lb->addObj( $user->getTalkPage() );
			}
		}
		$lb->setCaller( __METHOD__ );
		$lb->execute();
	}
}

interface UsernameQuery {
	/**
	 * @param string $wiki wiki id
	 * @param array $userIds List of user ids to lookup
	 * @return \ResultWrapper|bool Containing objects with user_id and
	 *   user_name properies.
	 */
	function execute( $wiki, array $userIds );
}

class TwoStepUsernameQuery implements UsernameQuery {
	public function __construct( DbFactory $dbFactory ) {
		$this->dbFactory = $dbFactory;
	}

	/**
	 * Look up usernames while respecting ipblocks with two queries
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return \ResultWrapper|bool
	 */
	public function execute( $wiki, array $userIds ) {
		$dbr = $this->dbFactory->getWikiDB( DB_SLAVE, array(), $wiki );
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
}

class OneStepUsernameQuery implements UsernameQuery {
	public function __construct( DbFactory $dbFactory ) {
		$this->dbFactory = $dbFactory;
	}

	/**
	 * Look up usernames while respecting ipblocks with one query.
	 * Unused, check to see if this is reasonable to use.
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return \ResultWrapper|null
	 */
	public function execute( $wiki, array $userIds ) {
		$dbr = $this->dbFactory->getWikiDb( DB_SLAVE, array(), $wiki );
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
