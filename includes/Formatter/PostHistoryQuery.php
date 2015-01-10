<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;

class PostHistoryQuery extends AbstractQuery {

	/**
	 * @param UUID $postId
	 * @return FormatterRow[]
	 * @throws InvalidDataException
	 */
	public function getResults( UUID $postId ) {
		$history = $this->storage->find(
			'PostRevision',
			array( 'rev_type_id' => $postId ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 100 )
		);
		if ( !$history ) {
			throw new InvalidDataException( 'Unable to load topic history for post ' . $postId->getAlphadecimal(), 'fail-load-history' );
		}

		$this->loadMetadataBatch( $history );
		$results = array();
		foreach ( $history as $revision ) {
			try {
				$results[] = $row = new FormatterRow;
				$this->buildResult( $revision, null, $row );
			} catch ( FlowException $e ) {
				\ExceptionHandler::logException( $e );
			}
		}

		return $results;
	}

}
