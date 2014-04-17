<?php

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Similar to CirrusSearch's forceSearchIndex, this will force indexing of Flow
 * data in ElasticSearch.
 *
 * @ingroup Maintenance
 */
class ForceSearchIndex extends Maintenance {
	// @todo: will need to steal a lot of code from Cirrus' ForceSearchIndex

	public function execute() {

	}
}

$maintClass = "ForceSearchIndex";
require_once RUN_MAINTENANCE_IF_MAIN;
