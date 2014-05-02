<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class EditTopicSummaryAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'edit-topic-summary' );
	}
}