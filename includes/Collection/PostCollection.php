<?php

namespace Flow\Collection;

use Flow\Model\AbstractRevision;

class PostCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\PostRevision';
	}

	public function getRevisionType() {
		return 'post';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		return $revision->getPostId();
	}
}
