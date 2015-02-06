<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;

class PostHistoryQuery extends AbstractQuery {

	/**
	 * @param UUID $postId
	 * @param int $limit
	 * @param UUID|null $offset
	 * @param string $direction 'rev' or 'fwd'
	 * @return FormatterRow[]
	 */
	public function getResults( UUID $postId, $limit = 50, UUID $offset = null, $direction = 'fwd' ) {
		$history = $this->storage->find(
			'PostRevision',
			array( 'rev_type_id' => $postId ),
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
		$results = array();
		foreach ( $history as $revision ) {
			try {
				$results[] = $row = new FormatterRow;
				$this->buildResult( $revision, null, $row );
			} catch ( FlowException $e ) {
				\MWExceptionHandler::logException( $e );
			}
		}

		return $results;
	}

}
