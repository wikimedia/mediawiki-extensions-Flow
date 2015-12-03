<?php

namespace Flow\Formatter;

use Flow\Data\Utils\SortRevisionsByRevisionId;
use Flow\Exception\FlowException;
use Flow\Model\UUID;
use MWExceptionHandler;

class BoardHistoryQuery extends HistoryQuery {
	/**
	 * @param UUID $workflowId
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $boardWorkflowId, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		$options = $this->getOptions( $direction, $limit, $offset );

		$headerHistory = $this->getHeaderResults( $boardWorkflowId, $options ) ?: array();
		$topicListHistory = $this->getTopicListResults( $boardWorkflowId, $options ) ?: array();
		$topicSummaryHistory = $this->getTopicSummaryResults( $boardWorkflowId, $options ) ?: array();

		$history = array_merge( $headerHistory, $topicListHistory, $topicSummaryHistory );
		usort( $history, new SortRevisionsByRevisionId( $options['order'] ) );

		if ( isset( $options['limit'] ) ) {
			$history = array_splice( $history, 0, $options['limit'] );
		}

		// We can't use ORDER BY ... DESC in SQL, because that will give us the
		// largest entries that are > some offset.  We want the smallest entries
		// that are > that offset, but ordered in descending order.  Core solves
		// this problem in IndexPager->getBody by iterating in descending order.
		if ( $direction === 'rev' ) {
			$history = array_reverse( $history );
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
		return $this->doInternalQueries(
			'PostRevisionBoardHistoryEntry',
			array(
				'topic_list_id' => $boardWorkflowId,
			),
			$options,
			self::POST_OVERFETCH_FACTOR
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
