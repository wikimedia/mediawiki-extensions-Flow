<?php

namespace Flow\Search\Iterators;

class HeaderIterator extends AbstractIterator {
	/**
	 * {@inheritDoc}
	 */
	protected function query() {
		// get the current (=most recent, =max) revision id for all headers
		return $this->dbr->select(
			array( 'flow_revision', 'flow_workflow' ),
			array( 'rev_id' => 'MAX(rev_id)', 'rev_type' ),
			$this->conditions,
			__METHOD__,
			array(
				'ORDER BY' => 'rev_id ASC',
				'GROUP BY' => 'rev_type_id',
			),
			array(
				'flow_workflow' => array(
					'INNER JOIN',
					array( 'workflow_id = rev_type_id' , 'rev_type' => 'header' )
				),
			)
		);
	}
}
