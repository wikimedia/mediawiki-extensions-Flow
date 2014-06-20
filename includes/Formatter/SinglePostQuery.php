<?php

namespace Flow\Formatter;

use Flow\Exception\FlowException;
use Flow\Model\UUID;

class SinglePostQuery extends AbstractQuery {
	/**
	 * @param UUID $postId
	 * @return FormatterRow[]
	 * @throws FlowException
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

		$formatterRow = null;
		$post = reset( $found );
		// Summary is only available to topic title now
		if ( $post->isTopicTitle() ) {
			$summary = $found = $this->storage->find(
				'PostSummary',
				array( 'rev_type_id' => $postId ),
				array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
			);
			if ( $summary ) {
				$formatterRow = new TopicRow();
				$formatterRow->summary = reset( $summary );
			}
		}

		return $this->buildResult( $post, null, $formatterRow );
	}
}
