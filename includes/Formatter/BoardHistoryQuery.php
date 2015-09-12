<?php

namespace Flow\Formatter;

use Flow\Data\Storage\RevisionStorage;
use Flow\Exception\FlowException;
use Flow\Model\UUID;
use MWExceptionHandler;

class BoardHistoryQuery extends AbstractQuery {
	/**
	 * @param UUID $workflowId
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $boardWorkflowId, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		$options = array(
			'sort' => 'rev_id',
			'order' => 'DESC',
			'limit' => $limit,
			'offset-id' => $offset,
			'offset-dir' => $direction,
			'offset-include' => false,
			'offset-elastic' => false,
		);

		$headerHistory = $this->getHeaderResults( $boardWorkflowId, $options ) ?: array();
		$topicListHistory = $this->getTopicListResults( $boardWorkflowId, $options ) ?: array();
		$topicSummaryHistory = $this->getTopicSummaryResults( $boardWorkflowId, $options ) ?: array();

		$history = $headerHistory + $topicListHistory + $topicSummaryHistory;

		// Having merged data from 3 sources, we now have to combine it
		// (according to the current sort & limit)
		// We can page through in any order, but a single page is always in descending order (later history first).
		krsort( $history );

		if ( isset( $options['limit'] ) ) {
			$history = array_splice( $history, 0, $options['limit'] );
		}

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

	protected function getHeaderResults( UUID $boardWorkflowId, $options ) {
		return $this->storage->find(
			'Header',
			array(
				'rev_type_id' => $boardWorkflowId,
			),
			$options
		);
	}

	protected function getTopicListResults( UUID $boardWorkflowId, $options ) {
		// Can't use 'PostRevision' because we need to get only the ones with the right board ID.
		return $this->storage->find(
			'PostRevisionBoardHistoryEntry',
			array(
				'topic_list_id' => $boardWorkflowId,
			),
			$options
		);
	}

	protected function getTopicSummaryResults( UUID $boardWorkflowId, $options ) {
		return $this->storage->find(
			'PostSummaryBoardHistoryEntry',
			array(
				'topic_list_id' => $boardWorkflowId,
			),
			$options
		);
	}
}
