<?php

namespace Flow\Maintenance;

use Flow\Container;
use Flow\Data\ObjectManager;
use Flow\Exception\InvalidInputException;
use LoggedUpdateMaintenance;
use MediaWiki\MediaWikiServices;
use WikiMap;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Populates ref_id in flow_wiki_ref & flow_ext_ref.
 *
 * @ingroup Maintenance
 */
class FlowPopulateRefId extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Populates ref_id in flow_wiki_ref & flow_ext_ref' );

		$this->setBatchSize( 300 );

		$this->requireExtension( 'Flow' );
	}

	protected function getUpdateKey() {
		return 'FlowPopulateRefId';
	}

	protected function doDBUpdates() {
		$types = [
			'flow_wiki_ref' => Container::get( 'storage.wiki_reference' ),
			'flow_ext_ref' => Container::get( 'storage.url_reference' ),
		];

		foreach ( $types as $table => $storage ) {
			$this->update( $storage );
		}

		$this->output( "Completed\n" );

		return true;
	}

	/**
	 * @param ObjectManager $storage
	 * @throws InvalidInputException
	 */
	protected function update( ObjectManager $storage ) {
		global $wgFlowCluster;

		$total = 0;

		$lbFactory = MediaWikiServices::getInstance()->getDBLoadBalancerFactory();

		while ( true ) {
			$references = (array)$storage->find(
				[ 'ref_id' => null, 'ref_src_wiki' => WikiMap::getCurrentWikiId() ],
				[ 'limit' => $this->getBatchSize() ]
			);
			if ( !$references ) {
				break;
			}

			$storage->multiPut( $references, [] );
			$total += count( $references );
			$this->output( "Ensured ref_id for " . $total . " " . get_class( $references[0] ) . " references...\n" );
			$lbFactory->waitForReplication( [ 'cluster' => $wgFlowCluster ] );
		}
	}
}

$maintClass = FlowPopulateRefId::class;
require_once RUN_MAINTENANCE_IF_MAIN;
