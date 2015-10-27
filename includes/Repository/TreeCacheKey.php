<?php

namespace Flow\Repository;


use Flow\Container;

class TreeCacheKey {

	/**
	 * Generate the following cache keys
	 *   flow:tree:subtree|parent|rootpath:<object id>:<cache version>
	 * For example:
	 *   flow:tree:parent:srkbd1u0mzz81x51:4.7
	 *
	 * @param string $treeType
	 * @param string $objectId
	 * @return string
	 */
	public static function build( $treeType, $objectId ) {
		return wfForeignMemcKey( 'flow', '', 'tree', $treeType, $objectId, Container::get( 'cache.version' ) );
	}
}