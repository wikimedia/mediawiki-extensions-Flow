<?php

namespace Flow\Search\Iterators;

class HeaderIterator extends AbstractIterator {
	/**
	 * @inheritDoc
	 */
	protected function query() {
		// get the current (=most recent, =max) revision id for all headers
		return $this->dbr->newSelectQueryBuilder()
			->select( [ 'rev_id' => 'MAX(rev_id)', 'rev_type' ] )
			->from( 'flow_revision' )
			->join( 'flow_workflow', null, [ 'workflow_id = rev_type_id', 'rev_type' => 'header' ] )
			->where( $this->conditions )
			->caller( __METHOD__ )
			->orderBy( 'rev_id' )
			->groupBy( 'rev_type_id' )
			->fetchResultSet();
	}
}
