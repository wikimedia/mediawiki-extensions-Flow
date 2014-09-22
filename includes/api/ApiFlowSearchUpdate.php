<?php

use Flow\Container;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;
use Flow\Search\Connection;
use Flow\Search\SearchEngine;

/**
 * This API will find the most recently indexed FLow document, find anything
 * that's newer & add those to the search index as well.
 *
 * @todo: We'll likely don't want this to be publicly available in the end.
 */
class ApiFlowSearchUpdate extends ApiFlowBaseGet {
	/**
	 * @var SearchEngine
	 */
	protected $searchEngine;

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'qu' );
		$this->searchEngine = new SearchEngine();
	}

	public function execute() {
		// find most recent result in search index
		$this->searchEngine->setLimitOffset( 1 );
		$this->searchEngine->setSort( 'update_timestamp_desc' );
		$status = $this->searchEngine->searchText( '*' );

		if ( !$status->isGood() ) {
			throw new InvalidDataException( $status->getMessage(), 'fail-search' );
		}

		/** @var \Elastica\ResultSet|null $result */
		$result = $status->getValue();
		// result can be null, if nothing was found
		$results = $result === null ? array() : $result->getResults();

		$fromId = null;
		// find most recent update & generate fromId based on it
		if ( isset( $results[0] ) ) {
			$data = $results[0]->getData();
			$timestamp = $data['update_timestamp'];
			$timestamp = wfTimestamp( TS_UNIX, $timestamp );
			$timestamp += 1; // $fromId is inclusive, so let's ignore this exact id
			$fromId = UUID::getComparisonUUID( $timestamp );
		}

		// Set the timeout for maintenance actions
		global $wgFlowSearchMaintenanceTimeout;
		Connection::setTimeout( $wgFlowSearchMaintenanceTimeout );

		$results = array(
			'total' => 0,
			'revisions' => array(),
		);
		$updaters = Container::get( 'searchindex.updaters' );
		foreach ( $updaters as $updater ) {
			$conditions = $updater->buildQueryConditions( $fromId, null, null );
			$revisions = $updater->getRevisions( $conditions, array() );
			$results['total'] += $updater->updateRevisions( $revisions, null, null );

			foreach ( $revisions as $revision ) {
				$results['revisions'][$updater->getType()][] = $revision->getCollectionId()->getAlphaDecimal();
			}
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $results );
	}

	public function getDescription() {
		return 'Updates Flow search index';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=search-update',
		);
	}

	protected function getBlockNames() {
		// irrelevant for search API
		return array();
	}

	protected function getAction() {
		// irrelevant for search API
		return '';
	}
}
