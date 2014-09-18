<?php

use Flow\Search\Maintenance\SearchIndexConfig;
use CirrusSearch\Maintenance\Maintenance;
use CirrusSearch\Maintenance\Validators\Validator;
use CirrusSearch\Maintenance\Validators\CacheWarmersValidator;
use CirrusSearch\Maintenance\Validators\ShardAllocationValidator;
use CirrusSearch\Maintenance\Validators\AnalyzersValidator;
use CirrusSearch\Maintenance\Validators\MappingValidator;
use CirrusSearch\Maintenance\Validators\IndexValidator;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

/**
 * Similar to CirrusSearch's UpdateOneSearchIndexConfig.
 *
 * @ingroup Maintenance
 */
class FlowSearchConfig extends Maintenance {
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

	// @todo: phpdoc for all of these
	private $indexType;
	private $startOver;
	private $indexBaseName;
	private $reindexAndRemoveOk;
	private $reindexProcesses;
	private $reindexAcceptableCountDeviation;
	private $reindexChunkSize;
	private $reindexRetryAttempts;
	private $printDebugCheckConfig;
	private $langCode;
	private $prefixSearchStartsWithAny;
	private $phraseSuggestUseText;
	private $bannedPlugins;
	private $optimizeIndexForExperimentalHighlighter;
	private $maxShardsPerNode;
	private $refreshInterval;
	private $availablePlugins;
	private $indexIdentifier;
	private $analysisConfigBuilder;
	private $tooFewReplicas;

	protected function setProperties() {
		global $wgLanguageCode,
			$wgCirrusSearchPhraseSuggestUseText,
			$wgCirrusSearchPrefixSearchStartsWithAnyWord,
			$wgCirrusSearchBannedPlugins,
			$wgCirrusSearchOptimizeIndexForExperimentalHighlighter,
			$wgCirrusSearchMaxShardsPerNode,
			$wgCirrusSearchRefreshInterval;

		// @todo: these properties don't exist yet...

		$this->indexType = $this->getOption( 'indexType' );
		$this->startOver = $this->getOption( 'startOver', false );
		$this->indexBaseName = $this->getOption( 'baseName', wfWikiId() );
		$this->reindexAndRemoveOk = $this->getOption( 'reindexAndRemoveOk', false );
		$this->reindexProcesses = $this->getOption( 'reindexProcesses', wfIsWindows() ? 1 : 5 );
		$this->reindexAcceptableCountDeviation = $this->parsePotentialPercent(
			$this->getOption( 'reindexAcceptableCountDeviation', '5%' ) );
		$this->reindexChunkSize = $this->getOption( 'reindexChunkSize', 100 );
		$this->reindexRetryAttempts = $this->getOption( 'reindexRetryAttempts', 5 );
		$this->printDebugCheckConfig = $this->getOption( 'debugCheckConfig', false );
		$this->langCode = $wgLanguageCode;
		$this->prefixSearchStartsWithAny = $wgCirrusSearchPrefixSearchStartsWithAnyWord;
		$this->phraseSuggestUseText = $wgCirrusSearchPhraseSuggestUseText;
		$this->bannedPlugins = $wgCirrusSearchBannedPlugins;
		$this->optimizeIndexForExperimentalHighlighter = $wgCirrusSearchOptimizeIndexForExperimentalHighlighter;
		$this->maxShardsPerNode = isset( $wgCirrusSearchMaxShardsPerNode[$this->indexType] ) ? $wgCirrusSearchMaxShardsPerNode[$this->indexType] : 'unlimited';
		$this->refreshInterval = $wgCirrusSearchRefreshInterval;

		$this->availablePlugins = $this->scanAvailablePlugins( $this->bannedPlugins );
		$this->indexIdentifier = $this->pickIndexIdentifierFromOption( $this->getOption( 'indexIdentifier', 'current' ), $this->getIndexTypeName() );
		$this->analysisConfigBuilder = $this->pickAnalyzer( $this->langCode, $this->availablePlugins );
		$this->tooFewReplicas = $this->reindexAndRemoveOk && ( $this->startOver || !$this->getIndex()->exists() );
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
			global $wgCirrusSearchIndexAllocation;
			$validators[] = new ShardAllocationValidator( $this->getIndex(), $wgCirrusSearchIndexAllocation, $this );
			return $validators;
		}

		$validators[] = new IndexValidator( $this->getIndex(), $this->getShardCount(), $this->maxShardsPerNode, $this->getShardCount(), $this->getReplicaCount(), $this->refreshInterval, $this->analysisConfigBuilder, $this->getMergeSettings(), $this );

		// @todo: indexSettings

		$validator = new AnalyzersValidator( $this->getIndex(), $this->analysisConfigBuilder, $this );
		$validator->printDebugCheckConfig( $this->printDebugCheckConfig );
		$validators[] = $validator;

		$validator = new MappingValidator( $this->getIndex(), $this->optimizeIndexForExperimentalHighlighter, $this->availablePlugins, $this->getMappingConfig()->buildConfig(), $this->getPageType(), $this->getNamespaceType(), $this );
		$validator->printDebugCheckConfig( $this->printDebugCheckConfig );
		$validators[] = $validator;

		$validators[] = new CacheWarmersValidator( $this->indexType, $this->getPageType(), $this );

		// @todo: specificAlias

		if ( $this->tooFewReplicas ) {
			// @todo: indexSettings
		}

		// @todo: allAlias

		if ( $this->tooFewReplicas ) {
			// @todo: indexSettings
		}

		return $validators;
	}

	public function execute() {
		global $wgPoolCounterConf;

		// Make sure we don't flood the pool counter
		unset( $wgPoolCounterConf['CirrusSearch-Search'] );
		// Set the timeout for maintenance actions
		$this->setConnectionTimeout();

		$this->setProperties();

		try {
			$indexTypes = $this->getAllIndexTypes();
			if ( !in_array( $this->indexType, $indexTypes ) ) {
				$this->error( 'indexType option must be one of ' .
					implode( ', ', $indexTypes ), 1 );
			}

			$this->checkElasticsearchVersion();

			$validators = $this->getValidators();
			foreach ( $validators as $validator ) {
				$status = $validator->validate();
				if ( !$status->isOK() ) {
					$this->error( $status->getMessage()->text(), 1 );
				}
			}

			$this->updateVersions();
			$this->indexNamespaces();
		} catch ( \Elastica\Exception\Connection\HttpException $e ) {
			$message = $e->getMessage();
			$this->output( "\nUnexpected Elasticsearch failure.\n" );
			$this->error( "Http error communicating with Elasticsearch:  $message.\n", 1 );
		} catch ( \Elastica\Exception\ExceptionInterface $e ) {
			$type = get_class( $e );
			$message = ElasticsearchIntermediary::extractMessage( $e );
			$trace = $e->getTraceAsString();
			$this->output( "\nUnexpected Elasticsearch failure.\n" );
			$this->error( "Elasticsearch failed in an unexpected way.  This is always a bug in CirrusSearch.\n" .
				"Error type: $type\n" .
				"Message: $message\n" .
				"Trace:\n" . $trace, 1 );
		}


// @todo: below is old; probably no longer relevant

		global $wgPoolCounterConf, $wgLanguageCode;

		$options = $this->mOptions;
		$options['langCode'] = $wgLanguageCode;
		// @todo: additional config

		// Make sure we don't flood the pool counter
		unset( $wgPoolCounterConf['Flow-Search'] );

		try {
			$config = new SearchIndexConfig( $options, $this );

			// Set the timeout for maintenance actions
			$config->setConnectionTimeout();

			// @todo: figure out which of the below we need (and how)
			$config->checkElasticsearchVersion();

			if ( $this->getOption( 'forceOpen', false ) ) {
				$config->openIndex();
				return;
			}

			if ( $this->getOption( 'justCacheWarmers', false ) ) {
				$config->validateCacheWarmers();
				return;
			}

			if ( $this->getOption( 'justAllocation', false ) ) {
				$config->validateShardAllocation();
				return;
			}

			$config->validateIndex();
			$config->validateAnalyzers();
			$config->validateMapping();
			$config->validateCacheWarmers();
			$config->validateAlias();
			$config->updateVersions();
		} catch ( \Exception $e ) {
			// @todo: what to catch & how to deal with it?
		}
	}
}

$maintClass = 'FlowSearchConfig';
require_once RUN_MAINTENANCE_IF_MAIN;
