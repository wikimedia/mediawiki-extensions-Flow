<?php
/**
 * Provide usernames filtered by per-wiki ipblocks. Batches together
 * database requests for multiple usernames when possible.
 */
namespace Flow\Repository;

use Flow\Model\UserTuple;
use User;

/**
 * Batch together queries for a bunch of wiki+userid -> username
 */
class UserNameBatch {
	/**
	 * @var UserName\UserNameQuery
	 */
	protected $query;

	/**
	 * @var array[] map from wikiid to list of userid's to request
	 */
	protected $queued = array();

	/**
	 * @var array[] 2-d map from wiki id and user id to display username or false
	 */
	protected $usernames = array();

	/**
	 * @param UserName\UserNameQuery $query
	 * @param array $queued map from wikiid to list of userid's to request
	 */
	public function __construct( UserName\UserNameQuery $query, array $queued = array() ) {
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
	 * @param UserTuple $tuple
	 */
	public function addFromTuple( UserTuple $tuple ) {
		$this->add( $tuple->wiki, $tuple->id, $tuple->ip );
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
	 * @param UserTuple $tuple
	 * @return string|boolean false if username is not found or display is suppressed
	 */
	public function getFromTuple( UserTuple $tuple ) {
		return $this->get( $tuple->wiki, $tuple->id, $tuple->ip );
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
