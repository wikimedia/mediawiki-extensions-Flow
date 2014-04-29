<?php

namespace Flow\Formatter;

use Flow\Exception\InvalidDataException;
use Flow\Model\UUID;

class PostSummaryQuery extends AbstractQuery {
	/**
	 * @param UUID[] $postIds
	 * @return FormatterRow
	 */
	public function getResult( UUID $postId ) {
		$section = new \ProfileSection( __METHOD__ );
		$found = $this->storage->find(
			'PostSummary',
			array( 'rev_type_id' => $postId ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);
		if ( !$found ) {
			return null;
		}
		$this->loadMetadataBatch( $found );

		return $this->buildResult( reset( $found ), null );
	}
}
