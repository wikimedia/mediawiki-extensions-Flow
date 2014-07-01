<?php

namespace Flow;

use Flow\DbFactory;
use Flow\Exception\DataModelException;
use User;

/**
 * Is there a core object for retriving multiple watchlist items?
 */
class WatchedItems {

	protected $user;
	protected $dbFactory;

	public function __construct( User $user, DbFactory $dbFactory ) {
		$this->user = $user;
		$this->dbFactory = $dbFactory;
	}

	/**
	 * @todo - exact format of the param is not sure
	 * @param string[] array of UUID string
	 */
	public function getWatchStatus( array $titles ) {
		$result = array_fill_keys( array_keys( $titles ), false );

		if ( !$titles ) {
			return $result;
		}
		if ( $this->user->getId() ) {
			return $result;
		}

		$dbr = $this->dbFactory->getDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'watchlist' ),
			array( 'wl_title' ),
			array(
				'wl_user' => $this->user->getId(),
				// @todo replace with Flow topic namespace
				'wl_namespace' => 999999,
				'wl_title' => $titles
			),
			__METHOD__
		);
		if ( !$res ) {
			throw new DataModelException( 'query failure', 'process-data' );
		}
		foreach ( $res as $row ) {
			$result[$row->wl_title] = true;
		}

		return $result;
	}
}
