<?php

namespace Flow\Model;

class HeaderCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\Header';
	}

	public function getIdColumn() {
		return 'header_workflow_id';
	}

	protected static function getIdFromRevision( AbstractRevision $revision ) {
		return $revision->getWorkflowId();
	}
}
