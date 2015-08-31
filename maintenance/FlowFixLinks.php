<?php

use Flow\Container;
use Flow\LinksTableUpdater;
use Flow\Model\Workflow;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );
// extending these - autoloader not yet wired up at the point these are interpreted
require_once( __DIR__ . '/../../../includes/utils/BatchRowWriter.php' );
require_once( __DIR__ . '/../../../includes/utils/RowUpdateGenerator.php' );

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
		$this->removeSpecialPages();
		$this->rebuildCoreTables();

		$this->output( "Completed\n" );

		return true;
	}

	protected function removeSpecialPages() {
		/** @var \Flow\Data\ObjectManager $storage */
		$storage = Container::get( 'storage.wiki_reference' );
		$links = $storage->find( array( 'ref_target_namespace' => -1 ) );
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

			$this->output( "Rebuilt links for " . count( $rows ) . " workflows...\n" );
			wfWaitForSlaves();
		}
	}
}

$maintClass = 'FlowFixLinks';
require_once( RUN_MAINTENANCE_IF_MAIN );
