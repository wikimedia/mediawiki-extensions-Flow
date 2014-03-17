<?php

namespace Flow\Data;

class PagerPage {
	function __construct( $results, $pagingLinks, $pager ) {
		$this->results = $results;
		$this->pagingLinks = $pagingLinks;
		$this->pager = $pager;
	}

	public function getPager() {
		return $this->pager;
	}

	public function getResults() {
		return $this->results;
	}

	public function getPagingLink( $direction ) {
		if ( isset( $this->pagingLinks[$direction] ) ) {
			return $this->pagingLinks[$direction];
		}

		return null;
	}

	public function getPagingLinks() {
		return $this->pagingLinks;
	}
}
