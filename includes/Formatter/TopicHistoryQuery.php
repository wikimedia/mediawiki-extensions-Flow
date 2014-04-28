<?php

namespace Flow\Formatter;

use Flow\Data\PagerPage;
use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;

class TopicHistoryQuery  extends AbstractQuery {
	/**
	 * @param UUID[] $postIds
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $postId ) {
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
		$results = array();
		foreach ( $history as $revision ) {
			try {
				$results[] = $row = new TopicRow;
				$this->buildResult( $revision, null, $row );
				$replyToId = $revision->getReplyToId();
				if ( $replyToId ) {
					// $revisionId into the key rather than value prevents
					// duplicate insertion
					$replies[$replyToId->getAlphadecimal()][$revision->getPostId()->getAlphadecimal()] = true;
				}
			} catch ( FlowException $e ) {
				\MWExceptionHandler::logException( $e );
			}
		}

		foreach ( $results as $result ) {
			$alpha = $result->revision->getPostId()->getAlphadecimal();
			$result->replies = isset( $replies[$alpha] ) ? array_keys( $replies[$alpha] ) : array();
		}

		return $results;
	}

}

class TopicRow extends FormatterRow {
	public $replies;
}
