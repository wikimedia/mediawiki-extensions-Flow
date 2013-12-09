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
	 * @param string[optional] $userName Non null to set known usernames like $wgUser
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

		// batch-load usernames from User cache
		$this->resolveFromCache( $wiki );

		// batch-load usernames from DB
		$this->resolveFromDB( $wiki );

		// usernames that could not be resolved = false
		$missing = array_diff( $this->queued[$wiki], array_keys( $this->usernames[$wiki] ) );
		foreach ( $missing as $userId ) {
			$this->usernames[$wiki][$userId] = false;
		}
		unset( $this->queued[$wiki] );
	}

	/**
	 * Resolve userids from DB
	 *
	 * @param string $wiki
	 */
	protected function resolveFromDB( $wiki ) {
		$queued = array_unique( $this->queued[$wiki] );

		// fetch data from database, in 3 queries
		$users = $this->getUsers( $queued, $wiki );
		$groups = $this->getUserGroups( $queued, $wiki );
		$options = $this->getUserOptions( $queued, $wiki );

		foreach ( $users as $user ) {
			$userId = $user->getId();
			$this->usernames[$userId] = $user->getName();

			// fill out some more data (User::$mCacheVars) & cache user obj
			$user->mGroups = $groups[$userId];
			$user->mOptionOverrides = $options[$userId];
			$user->mOptionsLoaded = true;

			$user->saveToCache();
		}
	}

	/**
	 * Resolve userids from User cache
	 *
	 * @param string $wiki
	 */
	protected function resolveFromCache( $wiki ) {
		$queued = array_unique( $this->queued[$wiki] );

		foreach ( $queued as $userId ) {
			$user = \User::newFromId( $userId );
			$cache = $user->loadFromCache();
			if ( $cache ) {
				$this->usernames[$userId] = $user->getName();
				unset( $this->queued[$wiki][$userId] );
			}
		}
	}

	/**
	 * Load data from table user
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return array
	 */
	protected function loadUsers( $wiki, array $userIds ) {
		$dbr = wfGetDB( DB_SLAVE, array(), $wiki );
		$rows = $dbr->select(
			'user',
			\User::selectFields(),
			array( 'user_id' => $userIds ),
			__METHOD__
		);

		$users = array();
		foreach ( $rows as $row ) {
			$users[$row->user_id] = \User::newFromRow( $row );
		}

		return $users;
	}

	/**
	 * Loads data from table user_groups
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return array
	 */
	protected function loadUserGroups( $wiki, array $userIds ) {
		$dbr = wfGetDB( DB_SLAVE, array(), $wiki );
		$rows = $dbr->select(
			'user_groups',
			array( 'ug_user', 'ug_group' ),
			array( 'ug_user' => $userIds ),
			__METHOD__
		);

		$groups = array();
		foreach ( $rows as $row ) {
			$groups[$row->ug_user][] = $row->ug_group;
		}

		return $groups;
	}

	/**
	 * Loads data from table user_properties
	 *
	 * @param string $wiki
	 * @param array $userIds
	 * @return array
	 */
	protected function loadUserOptions( $wiki, array $userIds ) {
		$dbr = wfGetDB( DB_SLAVE, array(), $wiki );
		$res = $dbr->select(
			'user_properties',
			array( 'up_user', 'up_property', 'up_value' ),
			array( 'up_user' => $userIds ),
			__METHOD__
		);

		$options = array();
		foreach ( $res as $row ) {
			$options[$row->up_user][$row->up_property] = $row->up_value;
		}

		return $options;
	}
}
