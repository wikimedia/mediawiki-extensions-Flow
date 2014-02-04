<?php

namespace Flow\Model;

class HeaderRevisionable extends LocalCacheAbstractRevisionable {
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
