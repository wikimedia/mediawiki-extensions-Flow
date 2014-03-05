<?php

namespace Flow\Collection;

use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;

class PostCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\PostRevision';
	}

	public function getIdColumn() {
		return 'tree_rev_descendant_id';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		if ( $revision instanceof PostRevision ) {
			return $revision->getPostId();
		} else {
			throw new FlowException( 'Expected PostRevision but received ' . get_class( $revision ) );
		}
	}
}
