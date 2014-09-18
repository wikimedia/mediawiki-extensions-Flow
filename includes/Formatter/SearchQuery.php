<?php

namespace Flow\Formatter;

use Elastica\Result;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;
use Flow\Search\Connection;

class SearchQuery extends AbstractQuery {
	/**
	 * @param Result[] $results
	 * @return FormatterRow[]
	 */
	public function getResults( array $results ) {
		// loop all results, fetch matched collections
		$collections = array();
		foreach ( $results as $result ) {
			try {
				$id = UUID::create( $result->getId() );
				$collections[] = $this->getCollection( $id, $result->getType() );
			} catch ( FlowException $e ) {
				wfWarn( __METHOD__ . ': ' . $e->getMessage() );
			}
		}

		// load all data for found results
		$results = array();
		foreach ( $collections as $collection ) {
			try {
				$revision = $collection->getLastRevision();
			} catch ( \Exception $e ) {
				wfWarn( __METHOD__ . ': Couldn\'t find last revision for ' . $collection->getId()->getAlphadecimal() );
			}

			$results[] = $this->buildResult( $revision, '' /* @todo: $indexField */ );
		}

		return $results;
	}

	/**
	 * Returns the collection object for the given id, based on the collection
	 * type, which can be determined based on the index type.
	 *
	 * @param UUID $id
	 * @param string $type
	 * @return AbstractCollection
	 */
	protected function getCollection( UUID $id, $type ) {
		// @todo: there's got to be more elegant ways to do this

		$map = array(
			Connection::TOPIC_TYPE_NAME => 'Flow\\Collection\\PostCollection',
			Connection::HEADER_TYPE_NAME => 'Flow\\Collection\\HeaderCollection',
		);

		if ( !isset( $map[$type] ) ) {
			throw new InvalidDataException( "Unknown index type: $type", 'fail-load-data' );
		}

		return $map[$type]::newFromId( $id );
	}
}
