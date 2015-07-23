<?php

use Flow\Container;
use Flow\Model\UUID;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
    ? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
    : dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * @ingroup Maintenance
 */
class FlowExternalStoreToText extends LoggedUpdateMaintenance {
    public function __construct() {
        parent::__construct();

        $this->mDescription = 'Backfills missing references in `text`.';

        $this->addOption( 'fromId', 'Start at a specific revision id (inclusive).', false, true );
        $this->addOption( 'toId', 'Stop at a specific revision (inclusive).', false, true );

        $this->setBatchSize( 300 );
    }

    protected function getUpdateKey() {
        return __CLASS__;
    }

    protected function doDBUpdates() {
        global $wgFlowExternalStore;
        if ( !$wgFlowExternalStore ) {
            // not using ExternalStore, this is irrelevant
            return true;
        }

        $container = Container::getContainer();
        $dbFactory = $container['db.factory'];
        $dbr = $dbFactory->getDb( DB_MASTER );

        $rowIterator = new EchoBatchRowIterator(
            $dbr,
            /* table = */'flow_revision',
            /* primary key = */'rev_id',
            $this->mBatchSize
        );
        $rowIterator->setFetchColumns( array( 'rev_content', 'rev_flags' ) );

        // allow from & to to be set for specific subsets
        $fromId = UUID::create( $this->getOption( 'fromId' ) );
        $toId = UUID::create( $this->getOption( 'toId' ) );
        if ( $fromId ) {
            $rowIterator->addConditions( array( 'rev_id >= ' . $dbr->addQuotes( $fromId->getBinary() ) ) );
        }
        if ( $toId ) {
            $rowIterator->addConditions( array( 'rev_id <= ' . $dbr->addQuotes( $toId->getBinary() ) ) );
        }

        $dbw = wfGetDB( DB_MASTER );
        $total = $fail = 0;
        foreach ( $rowIterator as $batch ) {
            $dbw->begin();

            foreach ( $batch as $row ) {
                $flags = explode( ',', $row->rev_flags );
                if ( !in_array( 'external', $flags ) ) {
                    // skip entries without ES url
                    continue;
                }

                $dbw->insert( 'text',
                    array(
                        'old_id' => $dbw->nextSequenceValue( 'text_old_id_seq' ),
                        'old_text' => $row->rev_content,
                        'old_flags' => $row->rev_flags,
                    ), __METHOD__
                );

                $total++;
            }

            $dbw->commit();
            wfWaitForSlaves();
        }

        $this->output( __CLASS__ . ": Processed a total of $total revisions.\n" );
        if ( $fail !== 0 ) {
            $this->error( "Errors were encountered while processing $fail of them.\n" );
        }

        return true;
    }
}

$maintClass = 'FlowExternalStoreToText';
require_once( RUN_MAINTENANCE_IF_MAIN );
