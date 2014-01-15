<?php

namespace Flow\Model;

use Flow\Exception\CrossWikiException;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use User;

class UserTuple {
	public $wiki;
	public $id;
	public $ip;

	public function __construct( $wiki, $id, $ip ) {
		if ( !is_integer( $id ) ) {
			if ( ctype_digit( $id ) ) {
				$id = (int)$id;
			} else {
				throw new InvalidDataException( 'User id must be an integer' );
			}
		}
		if ( $id < 0 ) {
			throw new InvalidDataException( 'User id must be >= 0' );
		}
		if ( !$wiki ) {
			throw new InvalidDataException( 'No wiki provided' );
		}
		if ( $id === 0 && strlen( $ip ) === 0 ) {
			throw new InvalidDataException( 'User has no id and no ip' );
		}
		if ( $id !== 0 && $ip !== null ) {
			throw new InvalidDataException( 'User has both id and ip' );
		}
		// @todo assert ip is ipv4 or ipv6, but do we really want
		// that on every anon user we load from storage?

		$this->wiki = $wiki;
		$this->id = $id;
		$this->ip = $ip;
	}

	public static function newFromUser( User $user ) {
		return new self(
			wfWikiId(),
			$user->getId(),
			$user->isAnon() ? $user->getName() : null
		);
	}

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

	public function toArray( $prefix = '' ) {
		return array(
			"{$prefix}wiki" => $this->wiki,
			"{$prefix}id" => $this->id,
			"{$prefix}ip" => $this->ip
		);
	}

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
