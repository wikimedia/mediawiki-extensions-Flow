<?php

namespace Flow\Formatter;

use Flow\Model\Workflow;
use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use ExceptionHandler;

class BoardHistoryQuery extends AbstractQuery {
	public function getResults( Workflow $workflow ) {
		// Load the history
		$history = $this->storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $workflow->getId() ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 300 )
		);

		if ( !$history ) {
			throw new InvalidDataException(
				'Unable to load topic list history for ' . $workflow->getId()->getAlphadecimal(),
				'fail-load-history'
			);
		}

		// pre-load our workflow
		$this->workflowCache[$workflow->getId()->getAlphadecimal()] = $workflow;
		// fetch any necessary metadata
		$this->loadMetadataBatch( $history );
		// build rows with the extra metadata
		$results = array();
		foreach ( $history as $revision ) {
			try {
				$result = $this->buildResult( $revision, 'rev_id' );
			} catch ( FlowException $e ) {
				$result = false;
				ExceptionHandler::logException( $e );
			}
			if ( $result ) {
				$results[] = $result;
			}
		}

		return $results;
	}
}
