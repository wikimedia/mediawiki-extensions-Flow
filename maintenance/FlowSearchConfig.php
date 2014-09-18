<?php

use Flow\Search\Connection;
use Flow\Search\Maintenance\MappingConfigBuilder;
use CirrusSearch\ElasticsearchIntermediary;
use CirrusSearch\Maintenance\AnalysisConfigBuilder;
use CirrusSearch\Maintenance\ConfigUtils;
use CirrusSearch\Maintenance\Maintenance;
use CirrusSearch\Maintenance\Reindexer;
use CirrusSearch\Maintenance\Validators\AnalyzersValidator;
use CirrusSearch\Maintenance\Validators\CacheWarmersValidator;
use CirrusSearch\Maintenance\Validators\IndexValidator;
use CirrusSearch\Maintenance\Validators\MappingValidator;
use CirrusSearch\Maintenance\Validators\ShardAllocationValidator;
use CirrusSearch\Maintenance\Validators\SpecificAliasValidator;
use CirrusSearch\Maintenance\Validators\Validator;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Similar to CirrusSearch's UpdateOneSearchIndexConfig.
 *
 * @ingroup Maintenance
 */
class FlowSearchConfig extends Maintenance {
	/**
	 * @var string
	 */
	protected $indexType;

	/**
	 * @var array
	 */
	protected $maxShardsPerNode;

	/**
	 * @var bool are we going to blow the index away and start from scratch?
	 */
	protected $startOver;

	/**
	 * @var int
	 */
	protected $refreshInterval;

	/**
	 * @var AnalysisConfigBuilder the builder for analysis config
	 */
	protected $analysisConfigBuilder;

	/**
	 * @var bool print config as it is being checked
	 */
	protected $printDebugCheckConfig;

	/**
	 * @var bool
	 */
	protected $optimizeIndexForExperimentalHighlighter;

	/**
	 * @var String[] list of available plugins
	 */
	protected $availablePlugins;

	/**
	 * @var bool are there too few replicas in the index we're making?
	 */
	protected $tooFewReplicas;

	/**
	 * @var bool
	 */
	protected $reindexAndRemoveOk;

	/**
	 * @var string
	 */
	protected $indexBaseName;

	/**
	 * @var string
	 */
	protected $indexIdentifier;

	/**
	 * @var int number of processes to use when reindexing
	 */
	protected $reindexProcesses;

	/**
	 * @var float how much can the reindexed copy of an index is allowed to deviate from the current
	 * copy without triggering a reindex failure
	 */
	protected $reindexAcceptableCountDeviation;

	/**
	 * @var int
	 */
	protected $reindexChunkSize;

	/**
	 * @var int
	 */
	protected $reindexRetryAttempts;

	/**
	 * @var array
	 */
	protected $bannedPlugins;

	/**
	 * @var string language code we're building for
	 */
	protected $langCode;

	/**
	 * @var array
	 */
	protected $indexAllocation;

	/**
	 * @var int
	 */
	protected $maintenanceTimeout;

	/**
	 * @var \ElasticaConnection
	 */
	protected $connection;

	/**
	 * @var ConfigUtils
	 */
	protected $utils;

	public function __construct() {
		parent::__construct();

		$this->addDescription( "Update the configuration or contents of one search index." );

		// Flow index types: Connection::TOPIC_TYPE_NAME or Connection::HEADER_TYPE_NAME
		$this->addOption( 'indexType', 'Index to update.  Either topic or header.', true, true ); // @todo: do I actually even need this one? indexes are different for Flow

		self::addSharedOptions( $this );
	}

	/**
	 * @param Maintenance $maintenance
	 */
	public static function addSharedOptions( $maintenance ) {
		// @todo: get rid of whatever we don't need

		$maintenance->addOption( 'startOver', 'Blow away the identified index and rebuild it with ' .
			'no data.' );
		$maintenance->addOption( 'forceOpen', "Open the index but do nothing else.  Use this if " .
			"you've stuck the index closed and need it to start working right now." );
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
		$maintenance->addOption( 'baseName', 'What basename to use for all indexes, ' .
			'defaults to wiki id', false, true );
		$maintenance->addOption( 'debugCheckConfig', 'Print the configuration as it is checked ' .
			'to help debug unexepcted configuration missmatches.' );
		$maintenance->addOption( 'justCacheWarmers', 'Just validate that the cache warmers are correct ' .
			'and perform no additional checking.  Use when you need to apply new cache warmers but ' .
			"want to be sure that you won't apply any other changes at an inopportune time." );
		$maintenance->addOption( 'justAllocation', 'Just validate the shard allocation settings.  Use ' .
			"when you need to apply new cache warmers but want to be sure that you won't apply any other " .
			'changes at an inopportune time.' );
	}

	protected function setProperties() {
		global $wgLanguageCode,
			$wgFlowSearchBannedPlugins,
			$wgFlowSearchOptimizeIndexForExperimentalHighlighter,
			$wgFlowSearchIndexAllocation,
			$wgFlowSearchMaintenanceTimeout,
			$wgFlowSearchRefreshInterval,
			$wgFlowSearchMaxShardsPerNode;

		$this->connection = Connection::getSingleton();
		$this->utils = new ConfigUtils( $this->getClient(), $this );

		$this->indexType = $this->getOption( 'indexType' );
		$this->startOver = $this->getOption( 'startOver', false );
		$this->indexBaseName = $this->getOption( 'baseName', wfWikiId() );
		$this->reindexAndRemoveOk = $this->getOption( 'reindexAndRemoveOk', false );
		$this->reindexProcesses = $this->getOption( 'reindexProcesses', wfIsWindows() ? 1 : 5 );
		$this->reindexChunkSize = $this->getOption( 'reindexChunkSize', 100 );
		$this->reindexRetryAttempts = $this->getOption( 'reindexRetryAttempts', 5 );
		$this->printDebugCheckConfig = $this->getOption( 'debugCheckConfig', false );

		$this->langCode = $wgLanguageCode;
		$this->bannedPlugins = $wgFlowSearchBannedPlugins;
		$this->optimizeIndexForExperimentalHighlighter = $wgFlowSearchOptimizeIndexForExperimentalHighlighter;
		$this->indexAllocation = $wgFlowSearchIndexAllocation;
		$this->maintenanceTimeout = $wgFlowSearchMaintenanceTimeout;
		$this->refreshInterval = $wgFlowSearchRefreshInterval;
		$this->maxShardsPerNode = isset( $wgFlowSearchMaxShardsPerNode[$this->indexType] ) ? $wgFlowSearchMaxShardsPerNode[$this->indexType] : 'unlimited';

		$this->indexIdentifier = $this->utils->pickIndexIdentifierFromOption( $this->getOption( 'indexIdentifier', 'current' ), $this->getIndexTypeName() );
		$this->reindexAcceptableCountDeviation = $this->utils->parsePotentialPercent( $this->getOption( 'reindexAcceptableCountDeviation', '5%' ) );
		$this->availablePlugins = $this->utils->scanAvailablePlugins( $this->bannedPlugins );
		$this->analysisConfigBuilder = $this->pickAnalyzer( $this->langCode, $this->availablePlugins );

		$this->tooFewReplicas = $this->reindexAndRemoveOk && ( $this->startOver || !$this->getIndex()->exists() );
	}

	/**
	 * A couple of commonly used (together) validators.
	 *
	 * @return Validator[]
	 */
	protected function getIndexSettingsValidators() {
		$validators = array();

		$validators[] = new \CirrusSearch\Maintenance\Validators\NumberOfShardsValidator( $this->getIndex(), $this->getShardCount(), $this );
		$validators[] = new \CirrusSearch\Maintenance\Validators\ReplicaRangeValidator( $this->getIndex(), $this->getReplicaCount(), $this );
		$validators[] = new \CirrusSearch\Maintenance\Validators\ShardAllocationValidator( $this->getIndex(), $this->indexAllocation, $this );
		$validators[] = new \CirrusSearch\Maintenance\Validators\MaxShardsPerNodeValidator( $this->getIndex(), $this->indexType, $this->maxShardsPerNode, $this );

		return $validators;
	}

	/**
	 * @return Validator[]
	 */
	protected function getValidators() {
		$validators = array();

		if ( $this->getOption( 'justCacheWarmers', false ) ) {
			$validators[] = new CacheWarmersValidator( $this->indexType, $this->getPageType(), $this );
			return $validators;
		}

		if ( $this->getOption( 'justAllocation', false ) ) {
			$validators[] = new ShardAllocationValidator( $this->getIndex(), $this->indexAllocation, $this );
			return $validators;
		}

		$validators[] = new IndexValidator( $this->getIndex(), $this->startOver, $this->maxShardsPerNode, $this->getShardCount(), $this->getReplicaCount(), $this->refreshInterval, $this->analysisConfigBuilder, $this->getMergeSettings(), $this );

		$validators = array_merge( $validators, $this->getIndexSettingsValidators() );

		$validator = new AnalyzersValidator( $this->getIndex(), $this->analysisConfigBuilder, $this );
		$validator->printDebugCheckConfig( $this->printDebugCheckConfig );
		$validators[] = $validator;

		$validator = new MappingValidator( $this->getIndex(), $this->optimizeIndexForExperimentalHighlighter, $this->availablePlugins, $this->getMappingConfig(), $this->getPageType(), $this->getNamespaceType(), $this );
		$validator->printDebugCheckConfig( $this->printDebugCheckConfig );
		$validators[] = $validator;

		$validators[] = new CacheWarmersValidator( $this->indexType, $this->getPageType(), $this );

		$reindexer = new Reindexer( $this->getIndex(), Connection::getSingleton(), $this->getPageType(), $this->getOldPageType(), $this->getShardCount(), $this->getReplicaCount(), $this->maintenanceTimeout, $this->getMergeSettings(), $this->getMappingConfig(), $this );
		$reindexParams = array( $this->reindexProcesses, $this->refreshInterval, $this->reindexRetryAttempts, $this->reindexChunkSize, $this->reindexAcceptableCountDeviation );
		$validators[] = new SpecificAliasValidator( $this->getClient(), $this->getIndexName(), $this->getSpecificIndexName(), $this->startOver, $reindexer, $reindexParams, $this->reindexAndRemoveOk, $this->tooFewReplicas, $this );

		if ( $this->tooFewReplicas ) {
			$validators = array_merge( $validators, $this->getIndexSettingsValidators() );
		}

		$validators[] = new \CirrusSearch\Maintenance\Validators\IndexAllAliasValidator( $this->getClient(), $this->getIndexName(), $this->getSpecificIndexName(), $this->startOver, $this->getIndexTypeName(), $this );
		if ( $this->tooFewReplicas ) {
			$validators = array_merge( $validators, $this->getIndexSettingsValidators() );
		}

		return $validators;
	}

	public function execute() {
		// Make sure we don't flood the pool counter
		global $wgPoolCounterConf;
		unset( $wgPoolCounterConf['Flow-Search'] );

		// Sets all of this class' properties that will be passed to validators
		$this->setProperties();

		// Set the timeout for maintenance actions
		$this->setConnectionTimeout();

		try {
			$indexTypes = $this->getAllIndexTypes();
			if ( !in_array( $this->indexType, $indexTypes ) ) {
				$this->error( 'indexType option must be one of ' .
					implode( ', ', $indexTypes ), 1 );
			}

			$this->utils->checkElasticsearchVersion();

			$validators = $this->getValidators();
			foreach ( $validators as $validator ) {
				$status = $validator->validate();
				if ( !$status->isOK() ) {
					$this->error( $status->getMessage()->text(), 1 );
				}
			}

//			$this->updateVersions(); // @todo: method doesn't yet exist
//			$this->indexNamespaces(); // @todo: method doesn't yet exist
		} catch ( \Elastica\Exception\Connection\HttpException $e ) {
			$message = $e->getMessage();
			$this->output( "\nUnexpected Elasticsearch failure.\n" );
			$this->error( "Http error communicating with Elasticsearch:  $message.\n", 1 );
		} catch ( \Elastica\Exception\ExceptionInterface $e ) {
			$type = get_class( $e );
			$message = ElasticsearchIntermediary::extractMessage( $e );
			$trace = $e->getTraceAsString();
			$this->output( "\nUnexpected Elasticsearch failure.\n" );
			$this->error( "Elasticsearch failed in an unexpected way.  This is always a bug in FlowSearch.\n" .
				"Error type: $type\n" .
				"Message: $message\n" .
				"Trace:\n" . $trace, 1 );
		}
	}

	// @todo: all of the below functions are copied from Cirrus' updateOneSearchIndexConfig - they need to be flowified still
	// @todo: I have a SearchIndexConfig.php file that has some of these already

	protected function getShardCount() {
		global $wgFlowSearchShardCount;

		if ( !isset( $wgFlowSearchShardCount[$this->indexType] ) ) {
			$this->error( 'Could not find a shard count for ' . $this->indexType . '.  Did you forget to add it ' .
				'to $wgFlowSearchShardCount?', 1 );
		}

		return $wgFlowSearchShardCount[$this->indexType];
	}

	protected function getReplicaCount() {
		global $wgFlowSearchReplicas;

		// If $wgFlowSearchReplicas is an array of index type to number of replicas then respect that
		if ( is_array( $wgFlowSearchReplicas ) ) {
			if ( isset( $wgFlowSearchReplicas[$this->indexType] ) ) {
				return $wgFlowSearchReplicas[$this->indexType];
			} else {
				$this->error( 'If wgFlowSearchReplicas is an array it must contain all index types.', 1 );
			}
		}

		// Otherwise its just a raw scalar so we should respect that too
		return $wgFlowSearchReplicas;
	}

	protected function getMergeSettings() {
		global $wgFlowSearchMergeSettings;

		if ( isset( $wgFlowSearchMergeSettings[$this->indexType] ) ) {
			return $wgFlowSearchMergeSettings[$this->indexType];
		}
		// If there aren't configured merge settings for this index type default to the content type.
		return $wgFlowSearchMergeSettings['content'];
	}

	/**
	 * @param string $langCode
	 * @param array $availablePlugins
	 * @return AnalysisConfigBuilder
	 */
	protected function pickAnalyzer( $langCode, array $availablePlugins = array() ) {
		// @todo: Cirrus AnalysisConfigBuilder ok, or do we need some Flow-specific extend?
		$analysisConfigBuilder = new AnalysisConfigBuilder( $langCode, $availablePlugins );
		$this->outputIndented( 'Picking analyzer...' . $analysisConfigBuilder->getDefaultTextAnalyzerType() . "\n" );
		return $analysisConfigBuilder;
	}

	/**
	 * @return array
	 */
	protected function getMappingConfig() {
		$builder = new MappingConfigBuilder( $this->optimizeIndexForExperimentalHighlighter );
		return $builder->buildConfig();
	}

	protected function getClient() {
		return $this->connection->getClient2();
	}

	protected function getIndex() {
		return $this->connection->getIndex2( $this->indexBaseName, $this->indexType, $this->indexIdentifier );
	}

	protected function getIndexName() {
		return $this->connection->getIndexName2( $this->indexBaseName );
	}

	protected function getSpecificIndexName() {
		return $this->connection->getIndexName2( $this->indexBaseName, $this->indexType, $this->indexIdentifier );
	}

	protected function getIndexTypeName() {
		return $this->connection->getIndexName2( $this->indexBaseName, $this->indexType );
	}

	protected function getPageType() {
		// @todo: what's "page type" equivalent for flow?
		return $this->getIndex()->getType( Connection::PAGE_TYPE_NAME );
	}

	protected function getNamespaceType() {
		// @todo: what's "namespace type" equivalent for flow?
		return $this->getIndex()->getType( Connection::NAMESPACE_TYPE_NAME );
	}

	protected function getOldPageType() {
		// @todo: this probably needs to be different for flow - has no "page type"?
		return Connection::getPageType( $this->indexBaseName, $this->indexType );
	}

	protected function getAllIndexTypes() {
		return Connection::getAllIndexTypes();
	}

	protected function setConnectionTimeout() {
		$this->connection->setTimeout2( $this->maintenanceTimeout );
	}
}

$maintClass = 'FlowSearchConfig';
require_once RUN_MAINTENANCE_IF_MAIN;
