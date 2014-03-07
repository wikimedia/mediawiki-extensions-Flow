<?php

namespace Flow\Model;

class SummaryCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\SummaryRevision';
	}

	public function getRevisionType() {
		return 'summary';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		return $revision->getSummaryId();
	}
}
