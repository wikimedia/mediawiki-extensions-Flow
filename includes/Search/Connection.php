<?php

namespace Flow\Search;

use Elastica\SearchableInterface;

/**
 * Provides the connection to the elasticsearch backend.
 */
class Connection extends \ElasticaConnection {
	/**
	 * Name of the topic type.
	 *
	 * @var string
	 */
	const TOPIC_TYPE_NAME = 'topic';

	/**
	 * Name of the header type.
	 *
	 * @var string
	 */
	const HEADER_TYPE_NAME = 'header';

	/**
	 * Name of the index that holds Flow data.
	 *
	 * @var string
	 */
	const FLOW_INDEX_TYPE = 'flow';

	/**
	 * @return string[]
	 */
	public function getServerList() {
		global $wgFlowSearchServers;
		return $wgFlowSearchServers;
	}

	/**
	 * @return int
	 */
	public function getMaxConnectionAttempts() {
		global $wgFlowSearchConnectionAttempts;
		return $wgFlowSearchConnectionAttempts;
	}

	/**
	 * DO NOT USE! I'm just leaving this in here for code in previous patch that
	 * has to be changed.
	 * @see comment on https://gerrit.wikimedia.org/r/#/c/126996/31/includes/Search/Connection.php
	 * @deprecated
	 * @return string[]
	 */
	public static function getAllIndexTypes() {
		return static::getAllTypes();
	}

	/**
	 * Get all indices we support.
	 *
	 * @return string[]
	 */
	public static function getAllIndices() {
		return array( static::FLOW_INDEX_TYPE );
	}

	/**
	 * Get all types we support.
	 *
	 * @return string[]
	 */
	public static function getAllTypes() {
		return array( static::TOPIC_TYPE_NAME, static::HEADER_TYPE_NAME );
	}

	/**
	 * @param string $name
	 * @param string|bool $type Type name or false to search entire index
	 * @return SearchableInterface
	 */
	public static function getRevisionType( $name, $type = false ) {
		$index = static::getSingleton()->getIndex2( $name, static::FLOW_INDEX_TYPE );

		if ( $type ) {
			$index = $index->getType( $type );
		}

		return $index;
	}
}
