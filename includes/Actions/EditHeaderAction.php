<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class EditHeaderAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'edit-header' );
	}
}