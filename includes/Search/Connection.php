<?php

namespace Flow\Search;

// @todo: phpdoc
class Connection extends \CirrusSearch\Connection {
	/**
	 * Name of the index that holds flow topics.
	 *
	 * @var string
	 */
	const TOPIC_INDEX_TYPE = 'flow_topic';

	/**
	 * Name of the index that holds Flow headers.
	 *
	 * @var string
	 */
	const HEADER_INDEX_TYPE = 'flow_header';

	/**
	 * Name of the revision type.
	 *
	 * @var string
	 */
	const REVISION_TYPE_NAME = 'flow';

	/**
	 * Get all index types we support.
	 *
	 * @return string[]
	 */
	public static function getAllIndexTypes() {
		return array( self::TOPIC_INDEX_TYPE, self::HEADER_INDEX_TYPE );
	}

	public static function getRevisionType( $name, $type = false ) {
		return self::getIndex( $name, $type )->getType( self::REVISION_TYPE_NAME );
	}

	// @todo: this will probably need a lot more code, I guess - still trying to figure everything out ;)
}
