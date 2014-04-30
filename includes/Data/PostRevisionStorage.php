<?php

namespace Flow\Data;

use Flow\Model\UUID;
use Flow\DbFactory;
use Flow\Repository\TreeRepository;
use Flow\Exception\DataModelException;

class PostRevisionStorage extends RevisionStorage {

	public function __construct( DbFactory $dbFactory, $externalStore, TreeRepository $treeRepo ) {
		parent::__construct( $dbFactory, $externalStore );
		$this->treeRepo = $treeRepo;
	}

	protected function joinTable() {
		return 'flow_tree_revision';
	}

	protected function joinField() {
		return 'tree_rev_id';
	}

	protected function getRevType() {
		return 'post';
	}

	protected function insertRelated( array $row ) {
		$tree = $this->splitUpdate( $row, 'tree' );
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			$this->joinTable(),
			$this->preprocessSqlArray( $tree ),
			__METHOD__
		);

		// If this is a brand new root revision it needs to be added to the tree
		// If it has a rev_parent_id then its already a part of the tree
		if ( $res && $row['rev_parent_id'] === null ) {
			$res = (bool) $this->treeRepo->insert(
				UUID::create( $tree['tree_rev_descendant_id'] ),
				UUID::create( $tree['tree_parent_id'] )
			);
		}

		if ( !$res ) {
			return false;
		}

		return $row;
	}

	// Topic split will primarily be done through the TreeRepository directly,  but
	// we will need to accept updates to the denormalized tree_parent_id field for
	// the new root post
	protected function updateRelated( array $changes, array $old ) {
		$treeChanges = $this->splitUpdate( $changes, 'tree' );

		// no changes to be performed
		if ( !$treeChanges ) {
			return $changes;
		}

		foreach( static::$obsoleteUpdateColumns as $val ) {
			// Need to use array_key_exists to check null value
			if ( array_key_exists( $val, $treeChanges ) ) {
				unset( $treeChanges[$val] );
			}
		}

		$extra = array_diff( array_keys( $treeChanges ), static::$allowedUpdateColumns );
		if ( $extra ) {
			throw new DataModelException( 'Update not allowed on: ' . implode( ', ', $extra ), 'process-data' );
		}

		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->update(
			$this->joinTable(),
			$this->preprocessSqlArray( $treeChanges ),
			array( 'tree_rev_id' => $old['tree_rev_id'] ),
			__METHOD__
		);

		if ( !$res ) {
			return false;
		}

		return $changes;
	}

	// this doesnt delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, its just a tree
	// and we arn't going to re-parent its children;
	protected function removeRelated( array $row ) {
		return $this->dbFactory->getDB( DB_MASTER )->delete(
			$this->joinTable(),
			array( $this->joinField() => $row['rev_id'] )
		);
	}
}
