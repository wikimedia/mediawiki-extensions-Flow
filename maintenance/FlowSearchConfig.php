<?php

use Flow\Search\Connection;
use Flow\Search\MappingConfigBuilder;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

// @todo: improve this; I can't just include a maintenance script (mainly because of its require_once RUN_MAINTENANCE_IF_MAIN)
require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/extensions/CirrusSearch/maintenance/updateOneSearchIndexConfig.php'
	: dirname( __FILE__ ) . '/../../../extensions/CirrusSearch/maintenance/updateOneSearchIndexConfig.php' );

/**
 * Similar to CirrusSearch's UpdateOneSearchIndexConfig.
 *
 * @ingroup Maintenance
 */
class FlowSearchConfig extends CirrusSearch\UpdateOneSearchIndexConfig {
	// @todo: do we need to steal more from Cirrus' ForceSearchIndex? What options are important?

	public function __construct() {
		parent::__construct();

		// override description for indexType to display Flow index types:
		// either Connection::TOPIC_TYPE_NAME or Connection::HEADER_TYPE_NAME
		$this->addOption( 'indexType', 'Index to update.  Either topic or header.', true, true );
	}

	/**
	 * @param Maintenance $maintenance
	 */
	public static function addSharedOptions( $maintenance ) {
/*
		$maintenance->addOption( 'startOver', 'Blow away the identified index and rebuild it with ' .
			'no data.' );
		$maintenance->addOption( 'forceOpen', "Open the index but do nothing else.  Use this if " .
			"you've stuck the index closed and need it to start working right now." );
*/
		$maintenance->addOption( 'indexIdentifier', "Set the identifier of the index to work on.  " .
			"You'll need this if you have an index in production serving queries and you have " .
			"to alter some portion of its configuration that cannot safely be done without " .
			"rebuilding it.  Once you specify a new indexIdentify for this wiki you'll have to " .
			"run this script with the same identifier each time.  Defaults to 'current' which " .
			"infers the currently in use identifier.  You can also use 'now' to set the identifier " .
			"to the current time in seconds which should give you a unique idenfitier.", false, true);
		$maintenance->addOption( 'reindexAndRemoveOk', "If the alias is held by another index then " .
			"reindex all documents from that index (via the alias) to this one, swing the " .
			"alias to this index, and then remove other index.  You'll have to redo all updates ".
			"performed during this operation manually.  Defaults to false." );
/*
		$maintenance->addOption( 'reindexProcesses', 'Number of processess to use in reindex.  ' .
			'Not supported on Windows.  Defaults to 1 on Windows and 5 otherwise.', false, true );
		$maintenance->addOption( 'reindexAcceptableCountDeviation', 'How much can the reindexed ' .
			'copy of an index is allowed to deviate from the current copy without triggering a ' .
			'reindex failure.  Defaults to 5%.', false, true );
		$maintenance->addOption( 'reindexChunkSize', 'Documents per shard to reindex in a batch.   ' .
			'Note when changing the number of shards that the old shard size is used, not the new ' .
			'one.  If you see many errors submitting documents in bulk but the automatic retry as ' .
			'singles works then lower this number.  Defaults to 100.', false, true );
		$maintenance->addOption( 'reindexRetryAttempts', 'Number of times to back off and retry ' .
			'per failure.  Note that failures are not common but if Elasticsearch is in the process ' .
			'of moving a shard this can time out.  This will retry the attempt after some backoff ' .
			'rather than failing the whole reindex process.  Defaults to 5.', false, true );
*/
		$maintenance->addOption( 'baseName', 'What basename to use for all indexes, ' .
			'defaults to wiki id', false, true );
/*
		$maintenance->addOption( 'debugCheckConfig', 'Print the configuration as it is checked ' .
			'to help debug unexepcted configuration missmatches.' );
		$maintenance->addOption( 'justCacheWarmers', 'Just validate that the cache warmers are correct ' .
			'and perform no additional checking.  Use when you need to apply new cache warmers but ' .
			"want to be sure that you won't apply any other changes at an inopportune time." );
		$maintenance->addOption( 'justAllocation', 'Just validate the shard allocation settings.  Use ' .
			"when you need to apply new cache warmers but want to be sure that you won't apply any other " .
			'changes at an inopportune time.' );
*/
	}

	public function execute() {
		global $wgPoolCounterConf,
			$wgFlowSearchMaintenanceTimeout,
			$wgLanguageCode;

		$this->indexType = $this->getOption( 'indexType' );
		$this->indexBaseName = $this->getOption( 'baseName', wfWikiId() );
		$this->indent = $this->getOption( 'indent', '' );
		$this->reindexAndRemoveOk = $this->getOption( 'reindexAndRemoveOk', false );
		$this->langCode = $wgLanguageCode;

		$indexTypes = Connection::getAllIndexTypes();
		if ( !in_array( $this->indexType, $indexTypes ) ) {
			$this->error( 'indexType option must be one of ' .
				implode( ', ', $indexTypes ), 1 );
		}

		// Make sure we don't flood the pool counter
		unset( $wgPoolCounterConf['Flow-Search'] ); // @todo: what's this about exactly?

		// Set the timeout for maintenance actions
		Connection::setTimeout( $wgFlowSearchMaintenanceTimeout );
		$client = Connection::getClient();

		try {
			$this->checkElasticsearchVersion( $client );
			$this->scanAvailablePlugins( $client );

			// @todo: ...

			$this->indexIdentifier = $this->pickIndexIdentifierFromOption( $this->getOption( 'indexIdentifier', 'current' ) );
			$this->pickAnalyzer();
//			$this->validateIndex();
			$this->validateAnalyzers();
			$this->validateMapping( $this->getIndex()->getType( $this->indexType ) );
//			$this->validateCacheWarmers();
			$this->validateAlias( $client );
//			$this->updateVersions();
		} catch ( \Exception $e ) {
			// @todo: what to catch & how to deal with it?
		}

		// @todo: steal more from Cirrus' UpdateOneSearchIndexConfig
	}

	/**
	 * @return \Elastica\Index being updated
	 */
	protected function getIndex() {
		return Connection::getIndex( $this->indexBaseName, Connection::FLOW_INDEX_TYPE, $this->indexIdentifier );
	}

	/**
	 * @param bool $optimizeForExperimentalHighlighter
	 * @return MappingConfigBuilder
	 */
	protected function getMappingConfigBuilder( $optimizeForExperimentalHighlighter ) {
		return new MappingConfigBuilder( $optimizeForExperimentalHighlighter );
	}
}

$maintClass = 'FlowSearchConfig';
require_once RUN_MAINTENANCE_IF_MAIN;
