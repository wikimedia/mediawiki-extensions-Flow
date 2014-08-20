<?php

namespace Flow\Model;

use Flow\Exception\CrossWikiException;
use Flow\Exception\FlowException;
use User;

class UserTuple {
	/**
	 * @var string
	 */
	public $wiki;

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $ip;

	/**
	 * @param string $wiki
	 * @param int $id
	 * @param string $ip
	 */
	public function __construct( $wiki, $id, $ip ) {
		$this->wiki = $wiki;
		$this->id = (int) $id;
		$this->ip = $ip;
	}

	/**
	 * @param User $user
	 * @return UserTuple
	 */
	public static function newFromUser( User $user ) {
		return new self(
			wfWikiId(),
			$user->getId(),
			$user->isAnon() ? $user->getName() : null
		);
	}

	/**
	 * @param array $user
	 * @param string $prefix
	 * @return UserTuple|null
	 */
	public static function newFromArray( array $user, $prefix = '' ) {
		$wiki = "{$prefix}wiki";
		$id = "{$prefix}id";
		$ip = "{$prefix}ip";

		if (
			isset( $user[$wiki] )
			&& array_key_exists( $id, $user ) && array_key_exists( $ip, $user )
			// $user[$id] === 0 is special case when when IRC formatter mocks up objects
			&& ( $user[$id] || $user[$ip] || $user[$id] === 0 )
		) {
			return new self( $user["{$prefix}wiki"], $user["{$prefix}id"], $user["{$prefix}ip"] );
		} else {
			return null;
		}
	}

	/**
	 * @param string $prefix
	 * @return array
	 */
	public function toArray( $prefix = '' ) {
		return array(
			"{$prefix}wiki" => $this->wiki,
			"{$prefix}id" => $this->id,
			"{$prefix}ip" => $this->ip
		);
	}

	/**
	 * @return User|false
	 * @throws FlowException
	 * @throws CrossWikiException
	 */
	public function createUser() {
		if ( $this->wiki !== wfWikiId() ) {
			throw new CrossWikiException( 'Can only retrieve same-wiki users' );
		}
		if ( $this->id ) {
			return User::newFromId( $this->id );
		} elseif ( !$this->ip ) {
			throw new FlowException( 'Either $userId or $userIp must be set.' );
		} else {
			return User::newFromName( $this->ip, /* $validate = */ false );
		}
	}
}
