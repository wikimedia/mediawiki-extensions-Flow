<?php

use Flow\Container;
use Flow\Data\ManagerGroup;
use Flow\Data\RevisionStorage;
use Flow\DbFactory;
use Flow\Model\UUID;
use Flow\Model\PostRevision;
use Flow\Search\Updater;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Similar to CirrusSearch's forceSearchIndex, this will force indexing of Flow
 * data in ElasticSearch.
 *
 * @ingroup Maintenance
 */
class FlowForceSearchIndex extends Maintenance {
	// @todo: will need to steal a lot of code from Cirrus' ForceSearchIndex
	// @todo: this script has stolen some code from ContributionsQuery - refactor so it can use same code

	/**
	 * @var DbFactory
	 */
	protected $dbFactory;

	/**
	 * @var ManagerGroup
	 */
	protected $storage;

	/**
	 * @var Updater
	 */
	protected $updater;

	public function execute() {
		$this->dbFactory = Container::get( 'db.factory' );
		$this->storage = Container::get( 'storage' );
		$this->updater = new Updater(); // @todo: get from Container;

		$fromId = $this->getOption( 'fromId', null );
		$fromId = $fromId ? UUID::create( $fromId ) : null;
		$toId = $this->getOption( 'toId', null );
		$toId = $toId ? UUID::create( $toId ) : null;
		$namespace = $this->getOption( 'namespace', null );
		$limit = $this->getOption( 'limit', null );

		// get query conditions & options based on given parameters
		$conditions = $this->buildConditions( $fromId, $toId, $namespace );
		$options = array();
		if ( $limit ) {
			$options['LIMIT'] = $limit;
		}

		// find requested revisions
		$revisions = $this->getTopics( $conditions, $options ) + $this->getHeaders( $conditions, $options );

		// @todo: figure out these params
		$flags = array();
		$shardTimeout = $clientSideTimeout = 0;

		$this->updater->updateRevisions( $revisions, $shardTimeout, $clientSideTimeout, $flags );
	}

	/**
	 * @param UUID|null $fromId
	 * @param UUID|null $toId
	 * @param int|null $namespace
	 * @return array
	 */
	protected function buildConditions( UUID $fromId = null, UUID $toId = null, $namespace = null ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$conditions = array();

		// only find entries in a given range
		if ( $fromId !== null ) {
			$conditions[] = 'rev_id > ' . $dbr->addQuotes( $fromId->getBinary() );
		}
		if ( $toId !== null ) {
			$conditions[] = 'rev_id <= ' . $dbr->addQuotes( $toId->getBinary() );
		}

		// find only within requested wiki/namespace
		$conditions['workflow_wiki'] = wfWikiId();
		if ( $namespace !== null ) {
			$conditions['workflow_namespace'] = $namespace;
		}

		return $conditions;
	}

	/**
	 * @param array $conditions
	 * @param array $options
	 * @return ResultWrapper
	 */
	protected function getTopics( array $conditions = array(), array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$rows = $dbr->select(
			array(
				'flow_revision', // revisions to find
				'flow_tree_revision', // resolve to post id
				'flow_tree_node', // resolve to root post (topic title)
				'flow_workflow', // resolve to workflow, to test if in correct wiki/namespace
			),
			array( '*' ),
			$conditions,
			__METHOD__,
			array( 'ORDER BY' => 'rev_id ASC' ) + $options,
			array(
				'flow_tree_revision' => array(
					'INNER JOIN',
					array( 'tree_rev_id = rev_id' )
				),
				'flow_tree_node' => array(
					'INNER JOIN',
					array(
						'tree_descendant_id = tree_rev_descendant_id',
						// the one with max tree_depth will be root,
						// which will have the matching workflow id
					)
				),
				'flow_workflow' => array(
					'INNER JOIN',
					array( 'workflow_id = tree_ancestor_id' )
				),
			)
		);

		// although we had to query for replies (topic titles don't change when
		// a reply is added) to make sure we have the most recent data, we
		// actually only want the root posts
		// @todo: there probably are better ways to fetch this ;)
		$roots = array();
		foreach ( $this->loadRevisions( $rows, 'PostRevision' ) as $revision ) {
			/** @var $revision PostRevision */
			$rootId = $revision->getCollection()->getRoot()->getId();
			$roots[$revision->getRevisionId()->getAlphadecimal()] = $rootId;
		}

		$rootPostLoader = Container::get( 'loader.root_post' );
		return $rootPostLoader->getMulti( $roots );
	}

	/**
	 * @param array $conditions
	 * @param array $options
	 * @return ResultWrapper
	 */
	protected function getHeaders( array $conditions = array(), array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_SLAVE );

		$rows = $dbr->select(
			array( 'flow_revision', 'flow_workflow' ),
			array( '*' ),
			$conditions,
			__METHOD__,
			array( 'ORDER BY' => 'rev_id ASC' ) + $options,
			array(
				'flow_workflow' => array(
					'INNER JOIN',
					array( 'workflow_id = rev_type_id' , 'rev_type' => 'header' )
				),
			)
		);

		return $this->loadRevisions( $rows, 'Header' );
	}

	/**
	 * Turns DB data into revision objects.
	 *
	 * @param ResultWrapper $rows
	 * @param string $revisionClass Class of revision object to build: PostRevision|Header
	 * @return array
	 */
	protected function loadRevisions( ResultWrapper $rows, $revisionClass ) {
		$revisions = array();
		foreach ( $rows as $row ) {
			$revisions[UUID::create( $row->rev_id )->getAlphadecimal()] = (array) $row;
		}

		// get content in external storage
		$revisions = RevisionStorage::mergeExternalContent( array( $revisions ) );
		$revisions = reset( $revisions );

		// we have all required data to build revision
		$mapper = $this->storage->getStorage( $revisionClass )->getMapper();
		$revisions = array_map( array( $mapper, 'fromStorageRow' ), $revisions );

		return $revisions;
	}
}

$maintClass = 'FlowForceSearchIndex';
require_once RUN_MAINTENANCE_IF_MAIN;
