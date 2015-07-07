<?php

namespace Flow\Data\Listener;

use DatabaseBase;
use Flow\Model\Workflow;
use SplQueue;
use Title;

/**
 * Duplicate workflow_last_updated_timestamp to page_props, so we can more
 * easily find the most recently active workflows on your watchlist, since
 * we can't otherwise join between watchlist & workflows (it may be on a
 * different DB/cluster)
 *
 * It is important that this listener only executes after a the page record
 * has been inserted. Currently, OccupationListener adds it to deferredQueue,
 * so make sure this is executed later!
 */
class PagePropsDuplicationListener extends AbstractListener {
    /**
     * @var DatabaseBase $dbw
     */
    protected $dbw;

    /**
     * @var SplQueue
     */
    protected $deferredQueue;

    /**
     * @param DatabaseBase $dbw
     * @param SplQueue $deferredQueue Queue of callbacks to run only if commit succeeds
     */
    public function __construct( DatabaseBase $dbw, SplQueue $deferredQueue ) {
        $this->dbw = $dbw;
        $this->deferredQueue = $deferredQueue;
    }

    /**
     * @param Workflow $object
     * @param array $new
     * @param array $metadata
     */
    public function onAfterInsert( $object, array $new, array $metadata ) {
        $this->upsert( $object );
    }

    /**
     * @param Workflow $object
     * @param array $old
     * @param array $new
     * @param array $metadata
     * @return array
     */
    public function onAfterUpdate( $object, array $old, array $new, array $metadata ) {
        if ( $old['workflow_last_update_timestamp'] !== $new['workflow_last_update_timestamp'] ) {
            $this->upsert( $object );
        }
    }

    /**
     * @param Workflow $workflow
     * @return array
     * @throws \TimestampException
     */
    protected function upsert( Workflow $workflow ) {
        $dbw = $this->dbw;
        $this->deferredQueue->push( function() use ( $dbw, $workflow ) {
            $pageId = $workflow->getArticleTitle()->getArticleID( Title::GAID_FOR_UPDATE );
            if ( !$pageId ) {
                // not a valid page id (anymore) = skip
                return;
            }

            $data = array(
                'pp_value' => $workflow->getLastModified(),
                // pp_sortkey is a float and may be lacking precision: let's store the
                // shortest time representation (unix timestamp) so we have something
                // meaningful to sort by
                'pp_sortkey' => (float) $workflow->getLastModifiedObj()->getTimestamp( TS_UNIX ),
            );

            $dbw->upsert(
                'page_props',
                $data + array(
                    'pp_page' => $pageId,
                    'pp_propname' => 'workflow_last_update_timestamp',
                ),
                array( 'pp_page', 'pp_propname' ),
                $data,
                __METHOD__
            );
        } );
    }
}
