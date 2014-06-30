<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class ComparePostRevisionsAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'compare-post-revisions' );
	}
}
