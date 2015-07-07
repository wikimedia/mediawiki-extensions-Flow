<?php

use Flow\Container;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
    ? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
    : dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );
require_once( __DIR__ . '/../../Echo/includes/BatchRowUpdate.php' );

/**
 * Duplicates workflow_last_update_timestamp into page_prop.
 *
 * @ingroup Maintenance
 */
class FlowUpdateTimestampPageProp extends LoggedUpdateMaintenance {
    public function __construct() {
        parent::__construct();

        $this->mDescription = 'Duplicates workflow_last_update_timestamp into page_prop';

        $this->setBatchSize( 300 );
    }

    protected function getUpdateKey() {
        return __CLASS__;
    }

    protected function doDBUpdates() {
        // reading from flow cluster, writing to core db
        $dbr = Container::get( 'db.factory' )->getDB( DB_SLAVE );
        $dbw = wfGetDB( DB_MASTER );

        $iterator = new EchoBatchRowIterator( $dbr, 'flow_workflow', 'workflow_id', $this->mBatchSize );
        $iterator->setFetchColumns( array( '*' ) );

        $updater = new EchoBatchRowUpdate(
            $iterator,
            new PagePropsUpdateWriter( $dbw, 'page_props' ),
            new PagePropsUpdateGenerator()
        );
        $updater->execute();

        return true;
    }
}

class PagePropsUpdateGenerator implements EchoRowUpdateGenerator {
    public function update( $row ) {
        $row = (array) $row;
        $workflow = \Flow\Model\Workflow::fromStorageRow( $row );

        $pageId = $workflow->getArticleTitle()->getArticleID();
        if ( !$pageId ) {
            // not a valid page id (anymore) = skip
            return array();
        }

        return array(
            'pp_page' => $pageId,
            'pp_propname' => 'workflow_last_update_timestamp',
            'pp_value' => $workflow->getLastModified(),
            'pp_sortkey' => $workflow->getLastModified(),
        );
    }
}

class PagePropsUpdateWriter extends EchoBatchRowWriter {
    public function write( array $updates ) {
        $this->db->begin();

        foreach ( $updates as $update ) {
            // EchoBatchRowWriter does an update on the primary key from
            // EchoBatchRowIterator. We're updating a different table than
            // the one we're reading from, so that PK doesn't apply here.
            // Since data may (will) not yet exist, we also want upsert
            // instead of update.
            $this->db->upsert(
                $this->table,
                $update['changes'],
                array( 'pp_page', 'pp_propname' ),
                $update['changes'],
                __METHOD__
            );
        }

        $this->db->commit();
        wfWaitForSlaves( false, false, $this->clusterName );
    }
}

$maintClass = 'FlowUpdateTimestampPageProp';
require_once( RUN_MAINTENANCE_IF_MAIN );
