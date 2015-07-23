<?php

use Flow\Container;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
    ? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
    : dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );
require_once( __DIR__ . '/../../Echo/includes/BatchRowUpdate.php' );

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
     * @return array
     */
    abstract protected function schema();

    public function __construct() {
        parent::__construct();

        $this->mDescription = 'Moves ExternalStore content from (a) particular cluster(s) to (an)other(s).';

        $this->addOption( 'from', 'ExternalStore cluster to move from (comma-separated).', true, true );
        $this->addOption( 'to', 'ExternalStore cluster to move to (comma-separated).', true, true );

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

        $iterator = new EchoBatchRowIterator( $dbr, $schema['table'], $schema['pk'], $this->mBatchSize );
        $iterator->setFetchColumns( array( $schema['content'], $schema['flags'] ) );

        $clusterConditions = array();
        foreach ( $from as $cluster ) {
            $clusterConditions[] = $schema['content'] . $dbr->buildLike( "DB://$cluster/", $dbr->anyString() );
        }
        $iterator->addConditions( array(
            $schema['flags'] . $dbr->buildLike( $dbr->anyString(), 'external', $dbr->anyString() ),
            $dbr->makeList( $clusterConditions, LIST_OR ),
        ) );

        $updater = new EchoBatchRowUpdate(
            $iterator,
            new EchoBatchRowWriter( $dbw, $schema['table'] ),
            new ExternalStoreUpdateGenerator( $to, $schema )
        );
        $updater->setOutput( array( $this, 'output' ) ); // @todo
        $updater->execute();

        // @todo: show some decent output
    }
}

class ExternalStoreUpdateGenerator implements EchoRowUpdateGenerator {
    /**
     * @var array
     */
    protected $stores = array();

    /**
     * @var array
     */
    protected $schema = array();

    /**
     * @param array $stores
     * @param array $schema
     */
    public function __construct( array $stores, array $schema ) {
        $this->stores = $stores;
        $this->schema = $schema;
    }

    /**
     * @param stdClass $row
     * @return array
     */
    public function update( $row ) {
        $content = ExternalStore::fetchFromURL( $row->{$this->schema['content']} );
        if ( $content === false ) {
            // @todo: error!
        }

        $flags = explode( ',', $row->{$this->schema['flags']} );

        $data = $this->insert( $content, $flags );
        return array(
            $this->schema['content'] => $data['content'],
            $this->schema['flags'] => $data['flags'],
        );
    }

    /**
     * @param string $content
     * @param array $flags
     * @return array
     * @throws Exception
     * @throws MWException
     * @throws bool
     */
    protected function insert( $content, array $flags = array() ) {
        if ( $content === '' ) {
            // don't store empty content elsewhere
            return array(
                'content' => $content,
                'flags' => array_diff( $flags, array( 'external' ) ),
            );
        }

        $url = ExternalStore::insertWithFallback( $this->stores, $content );
        if ( $url === false ) {
            // @todo: error!
        }

        $flags[] = 'external';

        return array(
            'content' => $url,
            'flags' => implode( ',', array_unique( $flags ) ),
        );
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
            'columns' => array( 'rev_content', 'rev_flags' ),
        );
    }
}

$maintClass = 'FlowExternalStoreMoveCluster';
require_once( RUN_MAINTENANCE_IF_MAIN );
