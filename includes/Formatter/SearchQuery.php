<?php

namespace Flow\Formatter;

use Elastica\Result;
use Flow\Collection\AbstractCollection;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;
use Flow\Search\Connection;
use MWExceptionHandler;

class SearchQuery extends AbstractQuery {
	/**
	 * @param Result[] $results
	 * @return FormatterRow[]
	 */
	public function getResults( array $results ) {
		// loop all results & fetch matched collections
		/** @var AbstractCollection[] $collections */
		$collections = array();
		foreach ( $results as $result ) {
			try {
				$id = UUID::create( $result->getId() );
				$collections[$id->getAlphadecimal()] = $this->getCollection( $id, $result->getType() );
			} catch ( FlowException $e ) {
				wfWarn( __METHOD__ . ': ' . $e->getMessage() );
				MWExceptionHandler::logException( $e );
			}
		}

		// load all data for found results
		// @todo: do more efficient batchloading instead of piecemeal Collection objects
		$results = array();
		foreach ( $collections as $id => $collection ) {
			try {
				$revision = $collection->getLastRevision();
			} catch ( \Exception $e ) {
				wfWarn( __METHOD__ . ': Couldn\'t find last revision for ' . $id . ': ' . $e->getMessage() );
				MWExceptionHandler::logException( $e );
				continue;
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
	 * @throws InvalidDataException
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
