<?php

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Flow\Collection\PostCollection;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Fixes Flow log entries.
 *
 * @ingroup Maintenance
 */
class FlowFixLog extends LoggedUpdateMaintenance {
	/**
	 * @var int
	 */
	protected $fixes = 0;

	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Fixes Flow log entries';

		$this->setBatchSize( 300 );
	}

	protected function getUpdateKey() {
		return 'FlowFixLog';
	}

	protected function doDBUpdates() {
		$continue = 0;
		while ( $continue !== false ) {
			$continue = $this->refreshBatch( $continue );

			// wait for core (we're updating user table) slaves to catch up
			wfWaitForSlaves();
		}

		$this->output( "Done fixing $this->fixes Flow log entries.\n" );

		return true;
	}

	public function refreshBatch( $continue = 0 ) {
		$dbr = wfGetDB( DB_SLAVE );
		$rows = $dbr->select(
			'logging',
			'*',
			array(
				'log_type' => array( 'delete', 'suppress' ),
				'log_action' => array(
					'flow-delete-post', 'flow-suppress-post', 'flow-restore-post',
					'flow-delete-topic', 'flow-suppress-topic', 'flow-restore-topic',
				),
				'log_id > ' . $dbr->addQuotes( $continue )
			),
			__METHOD__,
			array( 'LIMIT' => $this->mBatchSize )
		);

		// end of data
		if ( !$rows || $rows->numRows() === 0 ) {
			return false;
		}

		foreach ( $rows as $row ) {
			$updates = $this->fix( $row );
			if ( $updates ) {
				$this->fixes++;
				$this->update( $row->log_id, $updates );
			}

			// set value for next batch to continue at
			$continue = $row->log_id;
		}

		return $continue;
	}

	protected function fix( $row ) {
		$updates = array();

		$params = unserialize( $row->log_params );
		if ( !$params ) {
			// failed to unserialize = can't fix anything
			return array();
		}

		$collection = false;
		if ( isset( $params['topicId'] ) ) {
			$collection = $this->loadTopic( $params['topicId'] );
		} elseif ( isset( $params['postId'] ) ) {
			$collection = $this->loadPost( $params['postId'] );
		}

		if ( !$collection ) {
			// no topic or post id = can't fix anything
			return array();
		}

		try {
			// log_namespace & log_title used to be board, should be topic
			$updates['log_namespace'] = $collection->getTitle()->getNamespace();
			$updates['log_title'] = $collection->getTitle()->getDBkey();
		} catch ( \Exception $e ) {
			$updates = array();
		}

		// posts used to save revision id instead of post id...
		if ( isset( $params['postId'] ) ) {
			$params['postId'] = $collection->getId();
		}

		// re-serialize params (UUID used to serialize more verbose; might
		// as well shrink that down now that we're updating anyway...)
		$updates['log_params'] = serialize( $params );

		return $updates;
	}

	/**
	 * @param int $id
	 * @param array $updates
	 * @return bool
	 */
	protected function update( $id, array $updates ) {
		return wfGetDB( DB_MASTER )->update(
			'logging',
			$updates,
			array( 'log_id' => $id ),
			__METHOD__
		);
	}

	/**
	 * @param UUID $topicId
	 * @return PostCollection
	 */
	protected function loadTopic( UUID $topicId ) {
		return PostCollection::newFromId( $topicId );
	}

	/**
	 * @param UUID $postId
	 * @return PostCollection
	 */
	protected function loadPost( UUID $postId ) {
		try {
			$collection = PostCollection::newFromId( $postId );

			// validate collection by attempting to fetch latest revision - if
			// this fails (likely will for old data), catch will be invoked
			$collection->getLastRevision();
			return $collection;
		} catch ( \Exception $e ) {
			// posts used to mistakenly store revision ID instead of post ID

			/** @var ManagerGroup $storage */
			$storage = Container::get( 'storage' );
			$result = $storage->find(
				'PostRevision',
				array( 'rev_id' => $postId ),
				array( 'LIMIT' => 1 )
			);

			if ( $result ) {
				/** @var PostRevision $revision */
				$revision = reset( $result );

				// now build collection from real post ID
				return $this->loadPost( $revision->getPostId() );
			}
		}

		return false;
	}
}

$maintClass = 'FlowFixLog';
require_once( RUN_MAINTENANCE_IF_MAIN );
