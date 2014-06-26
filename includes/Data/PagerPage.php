<?php

namespace Flow\Data;

class PagerPage {
	function __construct( $results, $pagingLinkOptions, $pager ) {
		$this->results = $results;
		$this->pagingLinkOptions = $pagingLinkOptions;
		$this->pager = $pager;
	}

	public function getPager() {
		return $this->pager;
	}

	public function getResults() {
		return $this->results;
	}

	public function getPagingLinksOptions() {
		return $this->pagingLinkOptions;
	}
}
