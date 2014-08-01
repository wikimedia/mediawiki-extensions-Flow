<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Model\PostRevision;
use Flow\Model\UUID;

class TopicHistoryQuery  extends AbstractQuery {
	/**
	 * @param UUID $postId
	 * @return FormatterRow[]
	 * @throws InvalidDataException
	 */
	public function getResults( UUID $postId ) {
		/** @noinspection PhpUnusedLocalVariableInspection */
		$section = new \ProfileSection( __METHOD__ );
		$history = $this->storage->find(
			'TopicHistoryEntry',
			array( 'topic_root_id' => $postId ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
		if ( !$history ) {
			throw new InvalidDataException( 'Unable to load topic history for topic ' . $postId->getAlphadecimal(), 'fail-load-history' );
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
