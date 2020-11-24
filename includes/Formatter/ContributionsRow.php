<?php

namespace Flow\Formatter;

class ContributionsRow extends FormatterRow {
	/**
	 * @var string
	 */
	public $rev_timestamp;

	/**
	 * Used when the query uses the 'revision_actor_temp' table
	 *
	 * @var string
	 */
	public $revactor_timestamp;
}
