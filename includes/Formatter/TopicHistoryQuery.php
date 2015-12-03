<?php

namespace Flow\Formatter;

use Flow\Data\Utils\SortRevisionsByRevisionId;
use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\Model\UUID;

class TopicHistoryQuery extends HistoryQuery {
	/**
	 * @param UUID $topicRootId
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $topicRootId, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		$options = $this->getOptions( $direction, $limit, $offset );

		$topicPostHistory = $this->getTopicPostResults( $topicRootId, $options ) ?: array();
		$topicSummaryHistory = $this->getTopicSummaryResults( $topicRootId, $options ) ?: array();

		$history = array_merge( $topicPostHistory, $topicSummaryHistory );
		usort( $history, new SortRevisionsByRevisionId( $options['order'] ) );

		if ( isset( $options['limit'] ) ) {
			$history = array_splice( $history, 0, $options['limit'] );
		}

		// See explanation in BoardHistoryQuery::getResults.
		if ( $direction === 'rev' ) {
			$history = array_reverse( $history );
		}

		$this->loadMetadataBatch( $history );
		$results = $replies = array();
		foreach ( $history as $revision ) {
			try {
				$results[] = $row = new TopicRow;
				$this->buildResult( $revision, null, $row );
				if ( $revision instanceof PostRevision ) {
					$replyToId = $revision->getReplyToId();
					if ( $replyToId ) {
						// $revisionId into the key rather than value prevents
						// duplicate insertion
						$replies[$replyToId->getAlphadecimal()][$revision->getPostId()->getAlphadecimal()] = true;
					}
				}
			} catch ( FlowException $e ) {
				\MWExceptionHandler::logException( $e );
			}
		}

		foreach ( $results as $result ) {
			if ( $result->revision instanceof PostRevision ) {
				$alpha = $result->revision->getPostId()->getAlphadecimal();
				$result->replies = isset( $replies[$alpha] ) ? array_keys( $replies[$alpha] ) : array();
			}
		}

		return $results;
	}

	protected function getTopicPostResults( UUID $topicRootId, $options ) {
		// Can't use 'PostRevision' because we need to get only the ones with the right topic ID.
		return $this->doInternalQueries(
			'PostRevisionTopicHistoryEntry',
			array(
				'topic_root_id' => $topicRootId,
			),
			$options,
			self::POST_OVERFETCH_FACTOR
		);
	}

	protected function getTopicSummaryResults( UUID $topicRootId, $options ) {
		return $this->storage->find(
			'PostSummary',
			array(
				'rev_type_id' => $topicRootId,
			),
			$options
		);
	}
}
