<?php

use Flow\Container;
use Flow\Model\UUID;

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = dirname( __FILE__ ) . '/../../..';
}

require_once "$IP/maintenance/Maintenance.php";
require_once "$IP/includes/utils/RowUpdateGenerator.php";

/**
 * @ingroup Maintenance
 */
abstract class ExternalStoreMoveCluster extends Maintenance {
	/**
	 * Must return an array in the form:
	 * array(
	 *	'dbr' => DatabaseBase object,
	 *	'dbw' => DatabaseBase object,
	 *	'table' => 'flow_revision',
	 *	'pk' => 'rev_id',
	 *	'content' => 'rev_content',
	 *	'flags' => 'rev_flags',
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
	 *	AND <content> LIKE "DB://cluster/%";
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
		$this->addOption( 'dry-run', 'Outputs the old user content, inserts into new External Store, gives hypothetical new column values for flow_revision (but does not actually change flow_revision), and checks that old and new ES are the same.' );

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

		$updateGenerator = new ExternalStoreUpdateGenerator( $this, $to, $schema );

		if ( $this->hasOption( 'dry-run' ) ) {
			$this->output( "Starting dry run\n\n");
			foreach ( $iterator as $rows ) {
				$this->output( "Starting dry run batch\n" );
				foreach ( $rows as $row ) {
					$url = $row->{$schema['content']};
					$flags = explode( ',', $row->{$schema['flags']} );

					$oldContent = $updateGenerator->read( $url, $flags );
					$this->output( "\nOld content: $oldContent\n" );

					// Update itself just generates the update, it doesn't write
					// to flow_revision.
					$updatedColumns = $updateGenerator->update( $row );
					$this->output( "flow_revision columns would become:\n" );
					$this->output( var_export( $updatedColumns, true ) . "\n" );

					$newContent = $updatedColumns[$schema['content']];
					$newFlags = explode( ',', $updatedColumns[$schema['flags']] );
					if ( in_array( 'external', $newFlags, true ) ) {
						$newContent = $updateGenerator->read( $newContent, $newFlags );
					}

					if ( $newContent === $oldContent ) {
						$this->output( "New external store content matches old external store content\n" );
					} else {
						$revIdStr = UUID::create( $row->rev_id )->getAlphadecimal();
						$this->error( "New content for ID $revIdStr does not match prior content.\nNew content: $newContent\nOld content: $oldContent\n\nTerminating dry run.\n", 1 );
					}
				}

				$this->output( "\n\n" );
			}
			$this->output( "Dry run completed\n" );
			return;
		}

		$updater = new BatchRowUpdate(
			$iterator,
			new BatchRowWriter( $dbw, $schema['table'] ),
			$updateGenerator
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

class ExternalStoreUpdateGenerator implements RowUpdateGenerator {
	/**
	 * @var ExternalStoreMoveCluster
	 */
	protected $script;

	/**
	 * @var array
	 */
	protected $stores = array();

	/**
	 * @var array
	 */
	protected $schema = array();

	/**
	 * @param ExternalStoreMoveCluster $script
	 * @param array $stores
	 * @param array $schema
	 */
	public function __construct( ExternalStoreMoveCluster $script, array $stores, array $schema ) {
		$this->script = $script;
		$this->stores = $stores;
		$this->schema = $schema;
	}

	/**
	 * @param stdClass $row
	 * @return array
	 */
	public function update( $row ) {
		$url = $row->{$this->schema['content']};
		$flags = explode( ',', $row->{$this->schema['flags']} );

		try {
			$content = $this->read( $url, $flags );
			$data = $this->write( $content, $flags );
		} catch ( \Exception $e ) {
			// something went wrong, just output the error & don't update!
			$this->script->error( $e->getMessage(). "\n" );
			return array();
		}

		return array(
			$this->schema['content'] => $data['content'],
			$this->schema['flags'] => implode( ',', $data['flags'] ),
		);
	}

	/**
	 * @param string $url
	 * @param array $flags
	 * @return string
	 * @throws MWException
	 */
	public function read( $url, array $flags = array() ) {
		$content = ExternalStore::fetchFromURL( $url );
		if ( $content === false ) {
			throw new MWException( "Failed to fetch content from URL: $url" );
		}

		$content = \Revision::decompressRevisionText( $content, $flags );
		if ( $content === false ) {
			throw new MWException( "Failed to decompress content from URL: $url" );
		}

		return $content;
	}

	/**
	 * @param string $content
	 * @param array $flags
	 * @return array New ExternalStore data in the form of ['content' => ..., 'flags' => array( ... )]
	 * @throws MWException
	 */
	protected function write( $content, array $flags = array() ) {
		// external, utf-8 & gzip flags are no longer valid at this point
		$oldFlags = array_diff( $flags, array( 'external', 'utf-8', 'gzip' ) );

		if ( $content === '' ) {
			// don't store empty content elsewhere
			return array(
				'content' => $content,
				'flags' => $oldFlags,
			);
		}

		// re-compress (if $wgCompressRevisions is enabled) the content & set flags accordingly
		$flags = array_filter( explode( ',', \Revision::compressRevisionText( $content ) ) );

		// ExternalStore::insertWithFallback expects stores with protocol
		$stores = array();
		foreach ( $this->stores as $store ) {
			$stores[] = 'DB://' . $store;
		}
		$url = ExternalStore::insertWithFallback( $stores, $content );
		if ( $url === false ) {
			throw new MWException( 'Failed to write content to stores ' . json_encode( $stores ) );
		}

		// add flag indicating content is external again, and restore unrelated flags
		$flags[] = 'external';
		$flags = array_merge( $flags, $oldFlags );

		return array(
			'content' => $url,
			'flags' => array_unique( $flags ),
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
			'content' => 'rev_content',
			'flags' => 'rev_flags',
		);
	}
}

$maintClass = 'FlowExternalStoreMoveCluster';
require_once( RUN_MAINTENANCE_IF_MAIN );
