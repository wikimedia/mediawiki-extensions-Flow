<?php

namespace Flow\Search\Maintenance;

use Flow\Search\Connection;

class SearchIndexConfig extends \CirrusSearch\Maintenance\SearchIndexConfig {
	/**
	 * {@inheritDoc}
	 */
	protected function getMappingConfig() {
		$builder = new MappingConfigBuilder( $this->optimizeIndexForExperimentalHighlighter );
		return $builder->buildConfig();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIndex() {
		// @todo: what is $this->indexIdentifier about?
		return Connection::getIndex( $this->indexBaseName, Connection::FLOW_INDEX_TYPE ); //, $this->indexIdentifier );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getSpecificIndexName() {
		// @todo Connection::FLOW_INDEX_TYPE?
		return Connection::getIndexName( $this->indexBaseName, $this->indexType, $this->indexIdentifier );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getIndexTypeName() {
		return Connection::getIndexName( $this->indexBaseName, Connection::FLOW_INDEX_TYPE );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getIndexName() {
		return Connection::getIndexName( $this->indexBaseName );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getClient() {
		return Connection::getClient();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getAllIndexTypes() {
		return Connection::getAllIndexTypes();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getPageType() {
		return $this->getIndex()->getType( Connection::PAGE_TYPE_NAME );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOldPageType() {
		return Connection::getPageType( $this->indexBaseName, $this->indexType );
	}

	/**
	 * {@inheritDoc}
	 */
	public function setConnectionTimeout() {
		global $wgFlowSearchMaintenanceTimeout;
		Connection::setTimeout( $wgFlowSearchMaintenanceTimeout );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function destroySingleton() {
		Connection::destroySingleton();
	}
}
