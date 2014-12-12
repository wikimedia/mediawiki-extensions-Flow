<?php

namespace Flow\Data\Utils;

use EchoBatchRowIterator;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\UUID;
use Iterator;

class UserMerger {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @param DbFactory $dbFactory
	 * @param ManagerGroup $storage
	 */
	public function __construct( DbFactory $dbFactory, ManagerGroup $storage ) {
		$this->dbFactory = $dbFactory;
		$this->storage = $storage;
		$this->config = array(
			'flow_tree_revision' => array(
				'pk' => array( 'tree_rev_id' ),
				'userColumns' => array(
					'tree_orig_user_id' => 'getCreatorTuple',
				),
				'load' => array( $this, 'loadFromTreeRevision' ),
			),

			'flow_revision' => array(
				'pk' => array( 'rev_id' ),
				'userColumns' => array(
					'rev_user_id' => 'getUserTuple',
					'rev_mod_user_id' => 'getModeratedByTuple',
					'rev_edit_user_id' => 'getLastContentEditUserTuple',
				),
				'load' => array( $this, 'loadFromRevision' ),
				'loadColumns' => array( 'rev_type' ),
			),
		);
	}

	/**
	 * @return array
	 */
	public function getAccountFields() {
		$fields = array();
		$dbw = $this->dbFactory->getDb( DB_MASTER );
		foreach ( $this->config as $table => $config ) {
			$row = array(
				'db' => $dbw,
				$table,
			);
			foreach ( array_keys( $config['userColumns'] ) as $column ) {
				$row[] = $column;
			}
			$fields[] = $row;
		}
		return $fields;
	}

	/**
	 * Called after all databases have been updated. Needs to purge any
	 * cache that contained data about $oldUser
	 *
	 * @param integer $oldUserId
	 * @param integer $newUserId
	 */
	public function finalizeMerge( $oldUserId, $newUserId ) {
		$dbw = $this->dbFactory->getDb( DB_MASTER );
		foreach ( $this->config as $table => $config ) {
			foreach ( $config['userColumns'] as $column => $userTupleGetter ) {
				$it = new EchoBatchRowIterator( $dbw, $table, $config['pk'], 500 );
				// The database is migrated, so look for the new user id
				$it->addConditions( array( $column => $newUserId ) );
				if ( isset( $config['loadColumns'] ) ) {
					$it->setFetchColumns( $config['loadColumns'] );
				}
				$this->purgeTable( $it, $oldUserId, $config['load'], $userTupleGetter );
			}
		}
	}

	/**
	 * @param Iterator $it
	 * @param integer $oldUserId
	 * @param callable $callback Receives a single row, returns domain object or null
	 * @param string $userTupleGetter Method to call on domain object that will return
	 *  a UserTuple instance.
	 */
	protected function purgeTable( Iterator $it, $oldUserId, $callback, $userTupleGetter ) {
		foreach ( $it as $batch ) {
			foreach ( $batch as $pkRow ) {
				$obj = call_user_func( $callback, $pkRow );
				if ( !$obj ) {
					continue;
				}
				// This is funny looking because the loaded objects may have come from
				// the db with new user ids, or the cache with old user ids.
				// We need to tweak this object to look like the old user ids and then
				// purge caches so they get the old user id cache keys.
				$tuple = call_user_func( array( $obj, $userTupleGetter ) );
				if ( !$tuple ) {
					continue;
				}
				$tuple->id = $oldUserId;
				$om = $this->storage->getStorage( get_class( $obj ) );
				$om->clear();
				$om->merge( $obj );
				$om->cachePurge( $obj );
			}
			$this->storage->clear();
		}
	}

	/**
	 * @param object $row Single row from database
	 * @return PostRevision|null
	 */
	protected function loadFromTreeRevision( $row ) {
		return $this->storage->get( 'PostRevision', $row->tree_rev_id );
	}

	/**
	 * @param object $row Single row from database
	 * @return AbstractRevision|null
	 */
	protected function loadFromRevision( $row ) {
		$revTypes = array(
			'header' => 'Flow\Model\Header',
			'post-summary' => 'Flow\Model\PostSummary',
			'post' => 'Flow\Model\PostRevision',
		);
		if ( !isset( $revTypes[$row->rev_type] ) ) {
			wfDebugLog( 'Flow', __METHOD__ . ': Unknown revision type ' . $row->rev_type . ' did not merge ' . UUID::create( $row->rev_id )->getAlphadecimal() );
			return null;
		}

		return $this->storage->get( $revTypes[$row->rev_type], $row->rev_id );
	}
}
