<?php

namespace Flow\Data;

use Flow\Exception\DataModelException;

class HeaderRevisionStorage extends RevisionStorage {

	protected function joinTable() {
		return 'flow_header_revision';
	}

	protected function relatedPk() {
		return 'header_workflow_id';
	}

	protected function joinField() {
		return 'header_rev_id';
	}

	protected function insertRelated( array $row ) {
		$header = $this->splitUpdate( $row, 'header' );
		$res = $this->dbFactory->getDB( DB_MASTER )->insert(
			$this->joinTable(),
			$this->preprocessSqlArray( $header ),
			__METHOD__
		);
		if ( !$res ) {
			return false;
		}
		return $row;
	}

	// There is changable data in the header half, it just points to the correct workflow
	protected function updateRelated( array $changes, array $old ) {
		$headerChanges = $this->splitUpdate( $changes, 'header' );
		if ( $headerChanges ) {
			throw new DataModelException( 'No update allowed', 'process-data' );
		}
	}

	protected function removeRelated( array $row ) {
		return $this->dbFactory->getDB( DB_MASTER )->delete(
			$this->joinTable(),
			$this->preprocessSqlArray( array( $this->joinField() => $row['rev_id'] ) )
		);
	}
}
