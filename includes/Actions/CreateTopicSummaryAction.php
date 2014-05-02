<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class CreateTopicSummaryAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'create-topic-summary' );
	}
}