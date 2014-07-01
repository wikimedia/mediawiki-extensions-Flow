<?php

namespace Flow;

use DatabaseBase;
use Flow\Exception\DataModelException;
use Title;
use User;

/**
 * Is there a core object for retriving multiple watchlist items?
 */
class WatchedTopicItems {

	protected $user;
	protected $watchListDb;

	public function __construct( User $user, DatabaseBase $watchListDb ) {
		$this->user = $user;
		$this->watchListDb = $watchListDb;
	}

	/**
	 * @param string[] array of UUID string
	 */
	public function getWatchStatus( array $titles ) {
		$result = array_fill_keys( $titles, false );

		if ( !$this->user->getId() ) {
			return $result;
		}

		foreach ( $titles as $key => $title ) {
			$obj = Title::newFromText( $title, NS_TOPIC );
			if ( $obj ) {
				$titles[$key] = $obj->getDBkey();
			} else {
				unset( $titles[$key] );
			}
		}

		if ( !$titles ) {
			return $result;
		}

		$res = $this->watchListDb->select(
			array( 'watchlist' ),
			array( 'wl_title' ),
			array(
				'wl_user' => $this->user->getId(),
				'wl_namespace' => NS_TOPIC,
				'wl_title' => $titles
			),
			__METHOD__
		);
		if ( !$res ) {
			throw new DataModelException( 'query failure', 'process-data' );
		}
		foreach ( $res as $row ) {
			$result[strtolower( $row->wl_title )] = true;
		}
		return $result;
	}
}
