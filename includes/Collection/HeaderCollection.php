<?php

namespace Flow\Collection;

class HeaderCollection extends DelayedAbstractCollection {
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
