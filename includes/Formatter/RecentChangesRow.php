<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Data\Listener\RecentChangesListener;
use Flow\Exception\FlowException;
use Flow\FlowActions;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use RecentChange;

class RecentChangesRow extends FormatterRow {
	/**
	 * @var RecentChange
	 */
	public $recentChange;
}

