<?php

namespace Flow\Repository;

use DatabaseBase;

class SelectQueryBuilder {
	protected $table;
	protected $fields;
	protected $conditions = array();
	protected $pending = array(); // conditions that need to be escaped
	protected $options = array();
	protected $resultHandler;

	static public function create() {
		return new self;
	}

	public function from( $table ) {
		$this->table = $table;
		return $this;
	}

	public function select( $fields ) {
		$this->fields = (array) $fields;
		return $this;
	}
	public function where( $conditions ) {
		$this->conditions = (array) $conditions;
		return $this;
	}

	public function whereEquals( $field, $value ) {
		$this->conditions[$field] = $value;
		return $this;
	}

	public function andWhere( $conditions ) {
		$this->conditions = array_merge( $this->conditions, (array) $conditions );
		return $this;
	}

	// Less than or equals ( <= )
	public function andWhereLte( $field, $value ) {
		// needs to be escaped
		$this->pending[] = array( $field, '<=', $value );
	}

	public function options( $options ) {
		$this->options = (array) $options;
		return $this;
	}

	public function resultHandler( $callback ) {
		if ( !is_callable( $callback ) ) {
			throw new \Exception( 'Callback must be callable' );
		}
		$this->resultHandler = $callback;
		return $this;
	}

	public function query( DatabaseBase $db, $fname = __METHOD__ ) {
		$conditions = $this->conditions;
		foreach ( $this->pending as $row ) {
			list( $field, $op, $value ) = $row;
			$conditions[] = "$field $op	" . $dbr->addQuotes( $value );
		}
		$res = $db->select( $this->table, $this->fields, $conditions, $fname, $this->options );
		if ( $this->resultHandler ) {
			return call_user_func( $this->resultHandler, $res );
		}
		return $res;
	}
}
