<?php

namespace Flow\Log;

use Flow\Container;
use Flow\Formatter\AbstractQuery;
use Flow\Model\PostRevision;
use Flow\Model\UUID;

class LogQuery extends AbstractQuery {
	/**
	 * LogQuery is kind of a hack; I want to call loadMetadataBatch from
	 * Flow\Log\ActionFormatter, but that one extends core's way of doing
	 * log formatting already.
	 * I'm just gonna pull in whatever I need from container, not expect these
	 * dependencies to be properly passed along (because core creates the
	 * objects where we'd need this data)
	 */
	public function __construct() {
		$this->storage = Container::get( 'storage' );
		$this->treeRepository = Container::get( 'repository.tree' );
	}

	/**
	 * @param UUID[] $uuids
	 */
	public function loadMetadataBatch( $uuids ) {
		$posts = $this->loadPostsBatch( $uuids );
		parent::loadMetadataBatch( $posts );
	}

	/**
	 * @param UUID[] $uuids
	 * @return PostRevision[]
	 */
	protected function loadPostsBatch( array $uuids ) {
		$queries = array();
		foreach ( $uuids as $uuid ) {
			$queries[] = array( 'rev_type_id' => $uuid );
		}

		$found = $this->storage->findMulti(
			'PostRevision',
			$queries,
			array( 'sort' => 'rev_id', 'order' => 'DESC', 'limit' => 1 )
		);

		$revisions = array();
		foreach ( $found as $result ) {
			/** @var PostRevision $revision */
			$revision = reset( $result );
			$revisions[$revision->getPostId()->getAlphadecimal()] = $revision;
		}

		return $revisions;
	}
}
