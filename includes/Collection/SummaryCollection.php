<?php

namespace Flow\Model;

class TopicSummaryCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\TopicSummary';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		return $revision->getSummaryTargetId();
	}
}
