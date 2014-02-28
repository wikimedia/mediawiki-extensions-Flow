<?php

namespace Flow\Collection;

use Flow\Model\AbstractRevision;

class HeaderCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\Header';
	}

	public function getIdColumn() {
		return 'header_workflow_id';
	}

	public function getWorkflowId() {
		return $this->getId();
	}
}
