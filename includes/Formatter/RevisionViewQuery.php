<?php

namespace Flow\Formatter;

class RevisionViewQuery extends AbstractQuery {

	public function getSingleViewResult( UUID $revision ) {
		// @Todo - Pull the data from RevisionView
	}

	public function getDiffViewResult( UUID $new, UUID $prev = null ) {
		// @Todo - Pull the data from RevisionView
	}
}
