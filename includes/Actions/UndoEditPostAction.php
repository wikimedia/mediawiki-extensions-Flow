<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class UndoEditPostAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'undo-edit-post' );
	}
}
