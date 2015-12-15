<?php

use Flow\Container;
use Flow\LinksTableUpdater;
use Flow\Model\Workflow;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Fixes Flow References & entries in categorylinks & related tables.
 *
 * @ingroup Maintenance
 */
class FlowFixLinks extends LoggedUpdateMaintenance {
	public function __construct() {
		parent::__construct();

		$this->mDescription = 'Fixes Flow References & entries in categorylinks & related tables';

		$this->setBatchSize( 300 );
	}

	protected function getUpdateKey() {
		return __CLASS__ . ':v2';
	}

	protected function doDBUpdates() {
		// disable Echo notifications for this script
		global $wgEchoNotifications;

		$wgEchoNotifications = array();

		$this->removeVirtualPages();
		$this->rebuildCoreTables();

		$this->output( "Completed\n" );

		return true;
	}

	protected function removeVirtualPages() {
		/** @var \Flow\Data\ObjectManager $storage */
		$storage = Container::get( 'storage.wiki_reference' );
		$links = $storage->find( array(
			'ref_src_wiki' => wfWikiId(),
			'ref_target_namespace' => array( -1, -2 ),
		) );
		if ( $links ) {
			$storage->multiRemove( $links, array() );
		}

		$this->output( "Removed " . count( $links ) . " links to special pages.\n");
	}

	protected function rebuildCoreTables() {
		$dbw = wfGetDB( DB_MASTER );
		$dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
		/** @var \Flow\LinksTableUpdater $linksTableUpdater */
		$linksTableUpdater = Container::get( 'reference.updater.links-tables' );

		$iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', $this->mBatchSize );
		$iterator->setFetchColumns( array( '*' ) );
		$iterator->addConditions( array( 'workflow_wiki' => wfWikiId() ) );

		$count = 0;
		foreach ( $iterator as $rows ) {
			$dbw->begin();

			foreach ( $rows as $row ) {
				$workflow = Workflow::fromStorageRow( (array) $row );
				$id = $workflow->getArticleTitle()->getArticleID();

				// delete existing links from DB
				$dbw->delete( 'pagelinks', array( 'pl_from' => $id ), __METHOD__ );
				$dbw->delete( 'imagelinks', array( 'il_from' => $id ), __METHOD__ );
				$dbw->delete( 'categorylinks', array( 'cl_from' => $id ), __METHOD__ );
				$dbw->delete( 'templatelinks', array( 'tl_from' => $id ), __METHOD__ );
				$dbw->delete( 'externallinks', array( 'el_from' => $id ), __METHOD__ );
				$dbw->delete( 'langlinks', array( 'll_from' => $id ), __METHOD__ );
				$dbw->delete( 'iwlinks', array( 'iwl_from' => $id ), __METHOD__ );

				// regenerate & store those links
				$linksTableUpdater->doUpdate( $workflow );
			}

			$dbw->commit();

			$count += count( $rows );
			$this->output( "Rebuilt links for " . $count . " workflows...\n" );
			wfWaitForSlaves();
		}
	}
}

$maintClass = 'FlowFixLinks';
require_once( RUN_MAINTENANCE_IF_MAIN );
