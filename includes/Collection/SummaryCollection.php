<?php

namespace Flow\Model;

class PostSummaryCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\PostSummary';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		return $revision->getSummaryTargetId();
	}
}
