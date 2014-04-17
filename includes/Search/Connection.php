<?php

namespace Flow\Search;

// @todo: phpdoc
class Connection extends \ElasticaConnection {
	/**
	 * @return string[]
	 */
	public function getServerList() {
		// @todo: do we want to piggyback on this, or set up our own?
		global $wgCirrusSearchServers;
		return $wgCirrusSearchServers;
	}

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
	 * Get all revision types we support.
	 *
	 * @return string[]
	 */
	public static function getAllRevisionTypes() {
		return array( static::TOPIC_TYPE_NAME, static::HEADER_TYPE_NAME );
	}

	public static function getRevisionType( $name, $type = false ) {
		$index = static::getIndex( $name, static::FLOW_INDEX_TYPE );

		if ( $type ) {
			$index = $index->getType( $type );
		}

		return $index;
	}

	// @todo: this will probably need a lot more code, I guess - still trying to figure everything out ;)
}
