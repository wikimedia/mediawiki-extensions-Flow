<?php

namespace Flow;

use Flow\Data\BufferedCache;

/**
 * Super simple class,  provide the name of the "wiki" used for flow
 * data.  All classes within flow that need to access the db will go through
 * here
 */
class DbFactory {
	public function __construct( BufferedCache $bufferedCache, $wiki = false ) {
		$this->bufferedCache = $bufferedCache;
		$this->wiki = $wiki;
	}

	public function getDB( $db, $groups = array() ) {
		return wfGetDB( $db, $groups, $this->wiki );
	}

	public function getLB() {
		return wfGetLB( $this->wiki );
	}

	public function transactional( $callback ) {
		try {
			$dbw = $this->getDB( DB_MASTER );
			$dbw->begin();
			$this->bufferedCache->begin();
			$result = call_user_func( $callback, $dbw, $this->bufferedCache );
			$dbw->commit();
		} catch ( \Exception $e ) {
			if ( isset( $dbw ) ) {
				$dbw->rollback();
			}
			$this->bufferedCache->rollback();
			throw $e;
		}

		try {
			$this->bufferedCache->commit();
		} catch ( \Exception $e ) {
			// Commited to database but not to cache, now things are inconsistent.
			// What can be done?
			// We don't want to bail and can no longer rollback the main db, so we cant fail here
			wfWarn(  __METHOD__ . ': Commited to database but failed applying to cache' );
			MWExceptionHandler::logException( $e );
		}

		return $result;
	}
}
