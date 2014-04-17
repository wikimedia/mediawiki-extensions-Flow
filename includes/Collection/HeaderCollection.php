<?php

namespace Flow\Collection;

class HeaderCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\Header';
	}

	public function getWorkflowId() {
		return $this->getId();
	}

	public function getUpdater() {
		// @todo: not new, but from Container
		return new \Flow\Search\HeaderUpdater();
	}
}
