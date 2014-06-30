<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class CompareHeaderRevisionsAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'compare-header-revisions' );
	}
}
