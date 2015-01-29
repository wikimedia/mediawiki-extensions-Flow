<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use Flow\Model\Workflow;
use MWExceptionHandler;

class BoardHistoryQuery extends AbstractQuery {
	/**
	 * @param Workflow $workflow
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return array
	 */
	public function getResults( Workflow $workflow, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		// Load the history
		$history = $this->storage->find(
			'BoardHistoryEntry',
			array( 'topic_list_id' => $workflow->getId() ),
			array(
				'sort' => 'rev_id',
				'order' => 'DESC',
				'limit' => $limit,
				'offset-id' => $offset,
				'offset-dir' => $direction,
				'offset-include' => false,
				'offset-elastic' => false,
			)
		);

		if ( !$history ) {
			return array();
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
				MWExceptionHandler::logException( $e );
			}
			if ( $result ) {
				$results[] = $result;
			}
		}

		return $results;
	}
}
