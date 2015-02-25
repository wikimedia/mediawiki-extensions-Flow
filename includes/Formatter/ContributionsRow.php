<?php

namespace Flow\Formatter;

use BagOStuff;
use ContribsPager;
use Flow\Container;
use Flow\Data\Storage\RevisionStorage;
use Flow\DbFactory;
use Flow\Data\ManagerGroup;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;
use Flow\Exception\FlowException;
use ResultWrapper;
use User;

class ContributionsRow extends FormatterRow {
	public $rev_timestamp;
}
