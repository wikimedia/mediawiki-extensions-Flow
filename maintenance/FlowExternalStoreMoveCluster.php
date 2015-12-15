<?php

use Flow\Container;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * @ingroup Maintenance
 */
abstract class ExternalStoreMoveCluster extends Maintenance {
    /**
     * Must return an array in the form:
     * array(
     *     'dbr' => DatabaseBase object,
     *     'dbw' => DatabaseBase object,
     *     'table' => 'flow_revision',
     *     'pk' => 'rev_id',
     *     'content' => 'rev_content',
     *     'flags' => 'rev_flags',
     * )
     *
     * It will roughly translate into these queries, where PK is the
     * unique key to control batching & updates, content & flags are
     * the columns to read from & update with new ES data.
     * It will roughly translate into these queries:
     *
     * Against dbr: ('cluster' will be the argument passed to --from)
     * SELECT <pk>, <content>, <flags>
     * FROM <table>
     * WHERE <flags> LIKE "%external%"
     *   AND <content> LIKE "DB://cluster/%";
     *
     * Against dbw:
     * UPDATE <table>
     * SET <content> = ..., <flags> = ...
     * WHERE <pk> = ...;
     *
     * @return array
     */
    abstract protected function schema();

    public function __construct() {
        parent::__construct();

        $this->mDescription = 'Moves ExternalStore content from (a) particular cluster(s) to (an)other(s). Just make sure all clusters are valid $wgExternalServers.';

        $this->addOption( 'from', 'ExternalStore cluster to move from (comma-separated). E.g.: --from=cluster24,cluster25', true, true );
        $this->addOption( 'to', 'ExternalStore cluster to move to (comma-separated). E.g.: --to=cluster26', true, true );

        $this->setBatchSize( 300 );
    }

    public function execute() {
        $from = explode( ',', $this->getOption( 'from' ) );
        $to = explode( ',', $this->getOption( 'to' ) );

        $schema = $this->schema();
        /** @var DatabaseBase $dbr */
        $dbr = $schema['dbr'];
        /** @var DatabaseBase $dbw */
        $dbw = $schema['dbw'];

        $iterator = new BatchRowIterator( $dbr, $schema['table'], $schema['pk'], $this->mBatchSize );
        $iterator->setFetchColumns( array( $schema['content'], $schema['flags'] ) );

        $clusterConditions = array();
        foreach ( $from as $cluster ) {
            $clusterConditions[] = $schema['content'] . $dbr->buildLike( "DB://$cluster/", $dbr->anyString() );
        }
        $iterator->addConditions( array(
            $schema['flags'] . $dbr->buildLike( $dbr->anyString(), 'external', $dbr->anyString() ),
            $dbr->makeList( $clusterConditions, LIST_OR ),
        ) );

        $updater = new BatchRowUpdate(
            $iterator,
            new BatchRowWriter( $dbw, $schema['table'] ),
            new ExternalStoreUpdateGenerator( $this, $to, $schema )
        );
        $updater->setOutput( array( $this, 'output' ) );
        $updater->execute();
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

class FlowExternalStoreMoveCluster extends ExternalStoreMoveCluster {
    protected function schema() {
        $container = Container::getContainer();
        $dbFactory = $container['db.factory'];

        return array(
            'dbr' => $dbFactory->getDb( DB_SLAVE ),
            'dbw' => $dbFactory->getDb( DB_MASTER ),
            'table' => 'flow_revision',
            'pk' => 'rev_id',
            'content' => 'rev_content',
            'flags' => 'rev_flags',
        );
    }
}

$maintClass = 'FlowExternalStoreMoveCluster';
require_once( RUN_MAINTENANCE_IF_MAIN );
