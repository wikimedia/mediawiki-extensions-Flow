<?php

namespace Flow\Maintenance;

use BatchRowIterator;
use Flow\Container;
use Flow\Data\ObjectManager;
use Flow\LinksTableUpdater;
use Flow\Model\Workflow;
use MediaWiki\Maintenance\LoggedUpdateMaintenance;
use MediaWiki\WikiMap\WikiMap;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";

/**
 * Fixes Flow References & entries in categorylinks & related tables.
 *
 * @ingroup Maintenance
 */
class FlowFixLinks extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		$this->addDescription( 'Fixes Flow References & entries in categorylinks & related tables' );

		$this->setBatchSize( 300 );

		$this->requireExtension( 'Flow' );
	}

	protected function getUpdateKey() {
		return 'FlowFixLinks:v2';
	}

	protected function doDBUpdates() {
		// disable Echo notifications for this script
		global $wgEchoNotifications;

		$wgEchoNotifications = [];

		$this->removeVirtualPages();
		$this->rebuildCoreTables();

		$this->output( "Completed\n" );

		return true;
	}

	protected function removeVirtualPages() {
		/** @var ObjectManager $storage */
		$storage = Container::get( 'storage.wiki_reference' );
		$links = $storage->find( [
			'ref_src_wiki' => WikiMap::getCurrentWikiId(),
			'ref_target_namespace' => [ -1, -2 ],
		] );
		if ( $links ) {
			$storage->multiRemove( $links, [] );
		}

		$this->output( "Removed " . count( $links ) . " links to special pages.\n" );
	}

	protected function rebuildCoreTables() {
		$dbw = $this->getPrimaryDB();
		$dbr = Container::get( 'db.factory' )->getDB( DB_REPLICA );
		/** @var LinksTableUpdater $linksTableUpdater */
		$linksTableUpdater = Container::get( 'reference.updater.links-tables' );

		$iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', $this->getBatchSize() );
		$iterator->setFetchColumns( [ '*' ] );
		$iterator->addConditions( [ 'workflow_wiki' => WikiMap::getCurrentWikiId() ] );
		$iterator->setCaller( __METHOD__ );

		$count = 0;
		foreach ( $iterator as $rows ) {
			$this->beginTransaction( $dbw, __METHOD__ );

			foreach ( $rows as $row ) {
				$workflow = Workflow::fromStorageRow( (array)$row );
				$id = $workflow->getArticleTitle()->getArticleID();

				// delete existing links from DB
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'pagelinks' )
					->where( [ 'pl_from' => $id ] )
					->caller( __METHOD__ )
					->execute();
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'imagelinks' )
					->where( [ 'il_from' => $id ] )
					->caller( __METHOD__ )
					->execute();
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'categorylinks' )
					->where( [ 'cl_from' => $id ] )
					->caller( __METHOD__ )
					->execute();
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'templatelinks' )
					->where( [ 'tl_from' => $id ] )
					->caller( __METHOD__ )
					->execute();
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'externallinks' )
					->where( [ 'el_from' => $id ] )
					->caller( __METHOD__ )
					->execute();
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'langlinks' )
					->where( [ 'll_from' => $id ] )
					->caller( __METHOD__ )
					->execute();
				$dbw->newDeleteQueryBuilder()
					->deleteFrom( 'iwlinks' )
					->where( [ 'iwl_from' => $id ] )
					->caller( __METHOD__ )
					->execute();

				// regenerate & store those links
				$linksTableUpdater->doUpdate( $workflow );
			}

			$this->commitTransaction( $dbw, __METHOD__ );

			$count += count( $rows );
			$this->output( "Rebuilt links for " . $count . " workflows...\n" );
			$this->waitForReplication();
		}
	}
}

$maintClass = FlowFixLinks::class;
require_once RUN_MAINTENANCE_IF_MAIN;
