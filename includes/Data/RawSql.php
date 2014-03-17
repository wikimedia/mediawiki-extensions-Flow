<?php

namespace Flow\Data;

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
