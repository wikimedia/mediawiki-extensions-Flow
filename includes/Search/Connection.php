<?php

namespace Flow\Search;

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
	 * @var string[]
	 */
	protected $servers;

	/**
	 * @var int
	 */
	protected $maxConnectionAttempts;

	/**
	 * @param string[] $servers
	 * @param int $maxConnectionAttempts
	 */
	public function __construct( array $servers, $maxConnectionAttempts ) {
		$this->servers = $servers;
		$this->maxConnectionAttempts = $maxConnectionAttempts;
	}

	/**
	 * @return string[]
	 */
	public function getServerList() {
		return $this->servers;
	}

	/**
	 * @return int
	 */
	public function getMaxConnectionAttempts() {
		return $this->maxConnectionAttempts;
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
	 * @return \Elastica\Index
	 */
	public function getFlowIndex( $name ) {
		return $this->getIndex( $name, static::FLOW_INDEX_TYPE );
	}
}
