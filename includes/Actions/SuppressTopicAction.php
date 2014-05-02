<?php

namespace Flow\Actions;

use IContextSource;
use Page;

class SuppressTopicAction extends FlowAction {
	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'suppress-topic' );
	}
}