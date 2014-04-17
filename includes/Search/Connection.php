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
	 * Get all index types we support.
	 *
	 * @return string[]
	 */
	public static function getAllIndexTypes() {
		return array( self::TOPIC_INDEX_TYPE, self::HEADER_INDEX_TYPE );
	}

	public static function getPageType( $name, $type = false ) {
		// @todo: if we want to use Cirrus' Updater::sendDocuments without change, we'll need to do something with this method
		// @todo: not yet sure if we'll want to do that, or implement something similar ourselves
	}

	// @todo: this will probably need a lot more code, I guess - still trying to figure everything out ;)
}
