<?php

namespace Flow\Data;

/**
 * Value class wraps sql to be passed into queries.  Values
 * that are not wrapped in the RawSql class are escaped to
 * plain strings.
 */
class RawSql {
	function __construct( $sql ) {
		$this->sql = $sql;
	}

	function getSQL( $db ) {
		if ( is_callable( $this->sql ) ) {
			return call_user_func( $this->sql, $db );
		}

		return $this->sql;
	}
}
