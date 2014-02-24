<?php

namespace Flow\Model;

use Flow\Container;

class PostCollection extends LocalCacheAbstractCollection {
	public function getRevisionClass() {
		return 'Flow\\Model\\PostRevision';
	}

	public function getIdColumn() {
		return 'tree_rev_descendant_id';
	}

	public function getWorkflowId() {
		return $this->getRoot()->getId();
	}

	/**
	 * Returns the topic title collection this post is associated with.
	 *
	 * @return PostCollection
	 */
	public function getRoot() {
		/** @var \Flow\Repository\TreeRepository $treeRepo */
		$treeRepo = Container::get( 'repository.tree' );
		$uuid = $treeRepo->findRoot( $this->getId() );
		return static::newFromId( $uuid );
	}
}
