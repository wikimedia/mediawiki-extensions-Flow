<?php

namespace Flow\Formatter;

use Flow\Data\ManagerGroup;
use Flow\Exception\FlowException;
use Flow\Model\AbstractRevision;
use Flow\Model\Header;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use Flow\Repository\TreeRepository;
use ResultWrapper;

/**
 * Base class that collects the data necessary to utilize AbstractFormatter
 * based on a list of revisions. In some cases formatters will not utilize
 * this query and will instead receive data from a table such as the core
 * recentchanges.
 */
class FormatterRow {
	/** @var AbstractRevision */
	public $revision;
	/** @var AbstractRevision|null */
	public $previousRevision;
	/** @var AbstractRevision */
	public $currentRevision;
	/** @var Workflow */
	public $workflow;
	/** @var string */
	public $indexFieldName;
	/** @var string */
	public $indexFieldValue;
	/** @var PostRevision|null */
	public $rootPost;

	// protect against typos
	public function __get( $attribute ) {
		throw new \MWException( "Accessing non-existent parameter: $attribute" );
	}

	// protect against typos
	public function __set( $attribute, $value ) {
		throw new \MWException( "Accessing non-existent parameter: $attribute" );
	}
}

