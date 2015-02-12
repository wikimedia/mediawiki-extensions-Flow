<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\Model\UUID;

class TopicHistoryQuery  extends AbstractQuery {
	/**
	 * @param UUID $postId
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $postId, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		$history = $this->storage->find(
			'TopicHistoryEntry',
			array( 'topic_root_id' => $postId ),
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

}
