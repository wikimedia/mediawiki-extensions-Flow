<?php

namespace Flow\Model;

class HeaderCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\Header';
	}

	public function getRevisionType() {
		return 'header';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		return $revision->getWorkflowId();
	}
}
