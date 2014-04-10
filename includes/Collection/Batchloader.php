<?php

namespace Flow\Collection;

use Flow\Data\ObjectManager;
use Flow\Exception\InvalidDataException;

/**
 * This class will allow you to batch-load multiple queries "at once" (or well,
 * wherever possible; queries with different storages or options can't exactly
 * be performed at once)
 *
 * Queries can be scheduled by calling addFind() with the required query info.
 * Results can then be fetched by calling getResult(), again with the given
 * query data.
 */
class Batchloader {
	/**
	 * @var ObjectManager[]
	 */
	protected $storages = array();

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var array
	 */
	protected $scheduledQueries = array();

	/**
	 * @var array
	 */
	protected $executedQueries = array();

	/**
	 * @var array
	 */
	protected $results = array();

	/**
	 * Add a query to the list of queries to be batchloaded.
	 *
	 * @param string $className
	 * @param ObjectManager $storage
	 * @param array $attributes
	 * @param array $options
	 */
	public function addFind( $className, ObjectManager $storage, array $attributes, array $options = array() ) {
		$this->storages[$className] = $storage;

		// storage->findMulti will accept multiple queries, but options must be
		// the same for all queries, so divide all queries per options
		$optionsIndex = array_search( $options, $this->options );
		if ( $optionsIndex === false ) {
			$optionsIndex = count( $this->options );
			$this->options[$optionsIndex] = $options;
		}

		$this->scheduledQueries[$className][$optionsIndex][] = $attributes;
	}

	public function removeFind( $className, array $attributes, array $options = array() ) {
		if ( !isset( $this->scheduledQueries[$className] ) ) {
			return;
		}

		$optionsIndex = array_search( $options, $this->options );
		if ( $optionsIndex === false ) {
			return;
		}

		// find the query and unset it
		$queryIndex = array_search( $attributes, $this->scheduledQueries[$className][$optionsIndex] );
		unset( $this->scheduledQueries[$className][$optionsIndex][$queryIndex] );

		// don't care about unsetting lingering $storages & $options; they might
		// still be needed for other scheduled queries
	}

	/**
	 * Executes all scheduled queries.
	 */
	protected function execute() {
		foreach ( $this->scheduledQueries as $className => $data ) {
			foreach ( $data as $optionsIndex => $queries ) {
				$options = $this->options[$optionsIndex];
				$results = $this->storages[$className]->findMulti( $queries, $options );

				foreach ( $results as $i => $result ) {
					// indexes of query $results will be exactly the same as
					// indexes in $query - save results & queries, making sure
					// they're mapped to the same index so we can easily
					// retrieve the result by finding the query
					$query = $queries[$i];
					$this->executedQueries[$className][$optionsIndex][] = $query;
					$this->results[$className][$optionsIndex][] = $result;
				}
			}
		}

		// all queries have been executed; no point in running them again ;)
		$this->scheduledQueries = array();
	}

	/**
	 * Returns the result for a given query (make sure the query has been added
	 * via self::addFind)
	 *
	 * @see self::addFind
	 *
	 * @param string $className
	 * @param array $attributes
	 * @param array $options
	 * @return array
	 * @throws \Flow\Exception\InvalidDataException
	 */
	public function getResult( $className, array $attributes, array $options = array() ) {
		// execute all scheduled queries
		$this->execute();

		if ( !isset( $this->executedQueries[$className] ) ) {
			throw new InvalidDataException( 'Unknown query classname', 'fail-load-data' );
		}

		$optionsIndex = array_search( $options, $this->options );
		if ( $optionsIndex === false ) {
			throw new InvalidDataException( 'Unknown query option', 'fail-load-data' );
		}

		$queryIndex = array_search( $attributes, $this->executedQueries[$className][$optionsIndex] );
		if ( $queryIndex === false ) {
			throw new InvalidDataException( 'Unknown query attributes', 'fail-load-data' );
		}

		return $this->results[$className][$optionsIndex][$queryIndex];
	}
}
