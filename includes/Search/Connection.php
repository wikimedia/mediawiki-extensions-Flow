<?php

namespace Flow\Search;

// @todo: phpdoc
class Connection extends \CirrusSearch\Connection {
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
	 * Get all index types we support.
	 *
	 * @return string[]
	 */
	public static function getAllIndexTypes() {
		return array( self::FLOW_INDEX_TYPE );
	}

	public static function getRevisionType( $name, $type = false ) {
		$index = self::getIndex( $name, self::FLOW_INDEX_TYPE );

		if ( $type ) {
			$index = $index->getType( $type );
		}

		return $index;
	}

	// @todo: this will probably need a lot more code, I guess - still trying to figure everything out ;)
}
