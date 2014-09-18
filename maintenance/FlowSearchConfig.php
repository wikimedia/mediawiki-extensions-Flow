<?php

use Flow\Search\Connection;
use Flow\Search\MappingConfigBuilder;

require_once ( getenv( 'MW_INSTALL_PATH' ) !== false
	? getenv( 'MW_INSTALL_PATH' ) . '/maintenance/Maintenance.php'
	: dirname( __FILE__ ) . '/../../../maintenance/Maintenance.php' );

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
		// none; this is just to make sure not all of parent's options are added
	}

	public function execute() {
		global $wgPoolCounterConf, $wgFlowSearchMaintenanceTimeout;

		$this->indexType = $this->getOption( 'indexType' );
		$this->indent = $this->getOption( 'indent', '' );

		$indexTypes = Connection::getAllIndexTypes();
		if ( !in_array( $this->indexType, $indexTypes ) ) {
			$this->error( 'indexType option must be one of ' .
				implode( ', ', $indexTypes ), 1 );
		}

		// Make sure we don't flood the pool counter
		unset( $wgPoolCounterConf['Flow-Search'] ); // @todo: what's this about exactly?

		// Set the timeout for maintenance actions
		Connection::setTimeout( $wgFlowSearchMaintenanceTimeout );

		try {
			$client = Connection::getClient();
			$this->checkElasticsearchVersion( $client );
//			$this->scanAvailablePlugins( $client ); // @todo: need this?

			// @todo: ...

//			$this->indexIdentifier = $this->pickIndexIdentifierFromOption( $this->getOption( 'indexIdentifier', 'current' ) ); // @todo: not sure what this is
			$this->pickAnalyzer();
//			$this->validateIndex();
			$this->validateAnalyzers();
			$this->validateMapping( $this->getIndex()->getType( $this->indexType ) );
//			$this->validateCacheWarmers();
//			$this->validateAlias();
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
		// @todo: properly return correct index
		// @todo: static::FLOW_INDEX_TYPE instead of $this->indexType?
		return Connection::getIndex( $this->indexBaseName, static::FLOW_INDEX_TYPE, $this->indexIdentifier );
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
