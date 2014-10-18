<?php

namespace Flow\Data\Utils;

use EchoBatchRowIterator;
use Flow\Data\ManagerGroup;
use Flow\DbFactory;

class UserMerger {
	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ObjectManager
	 */
	protected $storage;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @param ManagerGroup $storage
	 */
	public function __construct( DbFactory $dbFactory, ManagerGroup $storage ) {
		$this->dbFactory = $dbFactory;
		$this->storage = $storage;
		$this->config = array(
			'flow_workflow' => array(
				'pk' => array( 'workflow_id' ),
				'userColumns' => array(
					'workflow_user_id' => 'getUserTuple',
				),
				'load' => array( $this, 'loadFromWorkflow' ),
			),

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
			$row = array( $table );
			foreach ( array_keys( $config['userColumns'] ) as $column ) {
				$row[] = $column;
			}
			$row['db'] = $dbw;
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
		foreach ( $this->config as $table => $config ) {
			$config += array( 'loadColumns' => array() );
			$this->purgeTable(
				$oldUser->getId(),
				$newUser->getId(),
				$table,
				$config['pk'],
				$config['userColumns'],
				$config['load'],
				$config['loadColumns']
			);
		}
	}

	/**
	 * @param integer $oldUserId
	 * @param integer $oldUserId
	 * @param string $table
	 * @parma string[] $pkFields Primary key fields to batch requests over
	 * @param string[] $columns Map from column names containing user ids to the domain
	 *  model method returning a UserTuple it loads into.
	 * @param callable $callback Receives a single row, returns domain object or null
	 * @param string[] $fetchColumns List of columns in addition to $pkFields to fetch
	 */
	protected function purgeTable(
		$newUserId,
		$oldUserId,	
		$table,
		array $pkFields,
		array $columns,
		$callback,
		array $fetchColumns = array()
	) {
		$dbw = $this->dbFactory->getDb( DB_MASTER );
		foreach ( $columns as $column => $userTupleGetter ) {
			$it = new EchoBatchRowIterator( $dbw, $table, $pkFields, 500 );
			// The database is migrated, so look for the new user id
			$it->addConditions( array( $column => $newUserId ) );
			$it->setFetchColumns( $fetchColumns );
			foreach ( $it as $batch ) {
				foreach ( $batch as $pkRow ) {
					$obj = call_user_func( $callback, $pkRow );
					if ( !$obj ) {
						continue;
					}
					$om = $this->storage->getStorage( get_class( $obj ) );
					// This is funny looking because the loaded objects may have come from
					// the db with new user ids, or the cache with old user ids.
					// We need to tweak this object to look like the old user ids and then 
					// purge caches so they get the old user id cache keys.
					$tuple = call_user_func( array( $obj, $userTupleGetter ) );
					$tuple->id = $oldUserId;
					$om->clear();
					$om->merge( $obj );
					$om->purgeCache( $obj );
				}
				$this->storage->clear();
			}
		}
	}

	protected function loadFromWorkflow( $row, $field, $newUserId ) {
		return $this->storage->get( 'Workflow', $row->workflow_id );
	}

	protected function loadFromTreeRevision( $row, $field, $newUserId ) {
		return $this->storage->get( 'PostRevision', $row->tree_rev_id );
	}

	protected function loadFromRevision( $row, $field, $newUserId ) {
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
