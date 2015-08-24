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
 * Fixes Flow entries in categorylinks & related tables.
 *
 * @ingroup Maintenance
 */
class FlowFixLinks extends LoggedUpdateMaintenance {
    public function __construct() {
        parent::__construct();

        $this->mDescription = 'Fixes Flow entries in categorylinks & related tables';

        $this->setBatchSize( 300 );
    }

    protected function getUpdateKey() {
        return __CLASS__;
    }

    protected function doDBUpdates() {
        $dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
        $iterator = new BatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', $this->mBatchSize );
        $iterator->setFetchColumns( array( '*' ) );
        $iterator->addConditions( array( 'workflow_wiki' => wfWikiId() ) );

        $updater = new BatchRowUpdate(
            $iterator,
            new UpdateLinksWriter( wfGetDB( DB_MASTER ), Container::get( 'reference.updater.links-tables' ) ),
            new WorkflowGenerator()
        );
        $updater->setOutput( array( $this, 'output' ) );
        $updater->execute();

        return true;
    }

    /**
     * parent::output() is a protected method, only way to access it from a
     * callback in php5.3 is to make a public function. In 5.4 can replace with
     * a Closure.
     *
     * @param string $out
     * @param mixed $channel
     */
    public function output( $out, $channel = null ) {
        parent::output( $out, $channel );
    }

    /**
     * parent::error() is a protected method, only way to access it from the
     * outside is to make it public.
     *
     * @param string $err
     * @param int $die
     */
    public function error( $err, $die = 0 ) {
        parent::error( $err, $die );
    }
}

class WorkflowGenerator implements RowUpdateGenerator {
    /**
     * @param stdClass $row
     * @return Workflow
     * @throws \Flow\Exception\DataModelException
     */
    public function update( $row ) {
        $row = (array) $row;
        return Workflow::fromStorageRow( $row );
    }
}

class UpdateLinksWriter extends BatchRowWriter {
    /**
     * @param DatabaseBase $db
     * @param LinksTableUpdater $linksTableUpdater
     * @param string|bool $clusterName
     */
    public function __construct( DatabaseBase $db, LinksTableUpdater $linksTableUpdater, $clusterName = false ) {
        parent::__construct( $db, 'bogus', $clusterName );
        $this->linksTableUpdater = $linksTableUpdater;
    }

    /**
     * Overwriting default writer because I'll receive an array of
     * Workflow objects that I will need to use to perform a bunch
     * of updates on a lot of different tables, using other bits
     * of existing code. It's not just 1 simple DB update.
     *
     * @param array[] $workflows
     */
    public function write( array $updates ) {
        $this->db->begin();

        foreach ( $updates as $update ) {
            /** @var Workflow $workflow */
            $workflow = $update['changes'];
            $id = $workflow->getArticleTitle()->getArticleID();

            // delete existing links from DB
            $this->db->delete( 'pagelinks', array( 'pl_from' => $id ), __METHOD__ );
            $this->db->delete( 'imagelinks', array( 'il_from' => $id ), __METHOD__ );
            $this->db->delete( 'categorylinks', array( 'cl_from' => $id ), __METHOD__ );
            $this->db->delete( 'templatelinks', array( 'tl_from' => $id ), __METHOD__ );
            $this->db->delete( 'externallinks', array( 'el_from' => $id ), __METHOD__ );
            $this->db->delete( 'langlinks', array( 'll_from' => $id ), __METHOD__ );
            $this->db->delete( 'iwlinks', array( 'iwl_from' => $id ), __METHOD__ );

            // regenerate & store those links
            $this->linksTableUpdater->doUpdate( $workflow );
        }

        $this->db->commit();
        wfWaitForSlaves( false, false, $this->clusterName );
    }
}

$maintClass = 'FlowFixLinks';
require_once( RUN_MAINTENANCE_IF_MAIN );
