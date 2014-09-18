<?php

use Flow\Container;
use Flow\Model\UUID;
use Flow\Search\Connection;
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
	// @todo: do we need to steal more from Cirrus' ForceSearchIndex? What options are important?

	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Force indexing Flow revisions (headers & topics).';

		$this->setBatchSize( 10 ); // @todo: we're not really doing anything with this yet

		$this->addOption( 'fromId', 'Start indexing at a specific revision id (inclusive).', false, true );
		$this->addOption( 'toId', 'Stop indexing at a specific revision (inclusive).', false, true );
		$this->addOption( 'limit', 'Maximum number of revisions to process before exiting the script. Default to unlimited.', false, true );
		$this->addOption( 'namespace', 'Only index revisions in this given namespace', false, true );
	}

	public function execute() {
		global $wgFlowSearchMaintenanceTimeout;

		// Set the timeout for maintenance actions
		Connection::getSingleton()->setTimeout2( $wgFlowSearchMaintenanceTimeout );

		$fromId = $this->getOption( 'fromId', null );
		$fromId = $fromId ? UUID::create( $fromId ) : null;
		$toId = $this->getOption( 'toId', null );
		$toId = $toId ? UUID::create( $toId ) : null;
		$namespace = $this->getOption( 'namespace', null );
		$limit = $this->getOption( 'limit', null );

		// get query conditions & options based on given parameters
		$options = array();
		if ( $limit ) {
			$options['LIMIT'] = $limit;
		}

		/** @var Updater[] $updaters */
		$updaters = Container::get( 'searchindex.updaters' );
		foreach ( $updaters as $updater ) {
			$conditions = $updater->buildQueryConditions( $fromId, $toId, $namespace );
			$revisions = $updater->getRevisions( $conditions, $options );
			$updater->updateRevisions( $revisions, null, null );
		}
	}
}

$maintClass = 'FlowForceSearchIndex';
require_once RUN_MAINTENANCE_IF_MAIN;
