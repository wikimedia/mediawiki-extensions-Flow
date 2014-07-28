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
	protected $overrides = array();

	public function __construct( User $user, DatabaseBase $watchListDb ) {
		$this->user = $user;
		$this->watchListDb = $watchListDb;
	}

	/**
	 * Helps prevent reading our own writes.  If we have explicitly
	 * watched this title in this request set it here instead of
	 * querying a slave and possibly not noticing due to slave lag.
	 */
	public function addOverrideWatched( Title $title ) {
		$this->overrides[$title->getNamespace()][$title->getDBkey()] = true;
	}

	/**
	 * @param string[] array of UUID string
	 */
	public function getWatchStatus( array $titles ) {
		$titles = array_unique( $titles );
		$result = array_fill_keys( $titles, false );

		if ( !$this->user->getId() ) {
			return $result;
		}

		foreach ( $titles as $key => $id ) {
			$obj = Title::makeTitleSafe( NS_TOPIC, $id );
			if ( $obj ) {
				$key = $obj->getDBkey();
				if ( isset( $this->overrides[$obj->getNamespace()][$key] ) ) {
					$result[$key] = true;
					unset( $titles[$key] );
				} else {
					$titles[$key] = $obj->getDBkey();
				}
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
