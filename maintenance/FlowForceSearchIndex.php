<?php

use Flow\Container;
use Flow\Model\UUID;

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

	public function execute() {
		$fromId = $this->getOption( 'fromId', null );
		$fromId = $fromId ? UUID::create( $fromId ) : null;
		$toId = $this->getOption( 'toId', null );
		$toId = $toId ? UUID::create( $toId ) : null;
		$namespace = $this->getOption( 'namespace', null );
		$limit = $this->getOption( 'limit', null );

		// @todo: implement deletes

		// get query conditions & options based on given parameters
		$options = array();
		if ( $limit ) {
			$options['LIMIT'] = $limit;
		}

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
