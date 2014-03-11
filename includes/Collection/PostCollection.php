<?php

namespace Flow\Collection;

use Flow\Container;
use Flow\Model\UUID;

class PostCollection extends LocalCacheAbstractCollection {
	/**
	 * @var UUID
	 */
	protected $rootId;

	public function getRevisionClass() {
		return 'Flow\\Model\\PostRevision';
	}

	public function getIdColumn() {
		return 'tree_rev_descendant_id';
	}

	public function getWorkflowId() {
		// the root post (topic title) has the same id as the workflow
		if ( !$this->rootId ) {
			/** @var \Flow\Repository\TreeRepository $treeRepo */
			$treeRepo = Container::get( 'repository.tree' );
			$this->rootId = $treeRepo->findRoot( $this->getId() );
		}

		return $this->rootId;
	}

	/**
	 * Returns the topic title collection this post is associated with.
	 *
	 * @return PostCollection
	 */
	public function getRoot() {
		return static::newFromId( $this->getWorkflowId() );
	}
}
