<?php

namespace Flow\Repository;

use Flow\Model\UUID;

class TreeCacheKey {

	/**
	 * Generate the following cache keys
	 *   flow:tree:subtree|parent|rootpath:<object id>:<cache version>
	 * For example:
	 *   flow:tree:parent:srkbd1u0mzz81x51:4.7
	 *
	 * @param string $treeType
	 * @param UUID $id
	 * @return string
	 */
	public static function build( $treeType, UUID $id ) {
		global $wgFlowCacheVersion;
		return wfForeignMemcKey( 'flow', '', 'tree', $treeType, $id->getAlphadecimal(), $wgFlowCacheVersion );
	}
}
