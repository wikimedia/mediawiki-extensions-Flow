<?php

namespace Flow\Formatter;

use Flow\Data\PagerPage;
use Flow\Exception\FlowException;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;

class SinglePostQuery extends AbstractQuery {
	/**
	 * @param UUID[] $postIds
	 * @return FormatterRow[]
	 */
	public function getResult( UUID $postId ) {
		$section = new \ProfileSection( __METHOD__ );
		$found = $this->storage->find(
			'PostRevision',
			array( 'rev_type_id' => $postId ),
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);
		if ( !$found ) {
			throw new FlowException( '@todo' );
		}
		$this->loadMetadataBatch( $found );

		return $this->buildResult( reset( $found ), null );
	}
}
