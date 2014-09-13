<?php

namespace Flow\Formatter;

use Flow\Model\PostRevision;
use Flow\Model\PostSummary;

class TopicRow extends FormatterRow {
	/**
	 * @var PostRevision[]
	 */
	public $replies;

	/**
	 * @var PostSummary
	 */
	public $summary;

	/**
	 * @var bool
	 */
	public $isWatched;
}
