<?php

namespace Flow\Formatter;

use Flow\Model\AbstractRevision;
use Flow\Model\PostRevision;
use Flow\Model\Workflow;
use LogicException;

/**
 * Helper class represents a row of data from AbstractQuery
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
	/** @var bool */
	public $isLastReply = false;
	/** @var bool */
	public $isFirstReply = false;

	/**
	 * Protect against typos
	 * @param string $attribute
	 * @return never
	 */
	public function __get( $attribute ) {
		throw new LogicException( "Accessing non-existent parameter: $attribute" );
	}

	/**
	 * Protect against typos
	 * @param string $attribute
	 * @param mixed $value
	 * @return never
	 */
	public function __set( $attribute, $value ) {
		throw new LogicException( "Accessing non-existent parameter: $attribute" );
	}
}
