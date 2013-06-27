<?php

namespace Flow\Data;

use Flow\DbFactory;
use Flow\Repository\TreeRepository;
use User;

abstract class RevisionStorage implements WritableObjectStorage {
	static protected $allowedUpdateColumns = array( 'rev_deleted' );
	protected $dbFactory;

	public function __construct( DbFactory $dbFactory ) {
		$this->dbFactory = $dbFactory;
	}

	abstract public function find( array $attributes, array $options = array() );

	public function findMulti( array $queries, array $options = array() ) {
		// TODO build proper query
		foreach ( $queries as $attributes ) {
			$result[] = $this->find( $attributes, $options );
		}
		return $result;
	}

	public function insert( array $row ) {
		list( $rev, $related ) = $this->splitUpdate( $row );
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			'flow_revision',
			$rev,
			__METHOD__
		);
		if ( !$res ) {
			// throw exception?
			return false;
		}

		return $this->insertRelated( $rev, $related );
	}

	abstract protected function insertRelated( array $rev, array $related );

	// This is to *UPDATE* a revision.  It should hardly ever be used.
	// For the most part should insert a new revision.  This will only be called
	// for oversighting?
	public function update( array $row, array $changeSet ) {
		$extra = array_diff( array_keys( $changeSet ), self::$allowedUpdateColumns );
		if ( $extra ) {
			throw new \MWException( 'Update not allowed on: ' . implode( ', ', $extra ) );
		}

		list( $rev, $related ) = $this->splitUpdate( $changeSet );

		if ( $rev ) {
			$dbw = $this->dbFactory->getDB( DB_MASTER );
			$res = $dbw->update(
				'flow_revision',
				$updates,
				array( 'rev_id' => $row['rev_id'] ),
				__METHOD__
			);
			// Throw exception on failure?
			if ( !( $res && $res->numRows() ) ) {
				return false;
			}
		}
		return $this->updateRelated( $rev, $related );
	}

	abstract protected function updateRelated( array $rev, array $related );

	// Revisions can only be removed for LIMITED circumstances,  in almost all cases
	// the offending revision should be updated with appropriate suppression.
	// Also note this doesnt delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, it will just show up as
	// [deleted] or something
	public function remove( array $row ) {
		$res = $this->dbFactory->getDB( DB_MASTER )->delete(
			'flow_revision',
			array( 'rev_id' => $row['rev_id'] ),
			__METHOD__
		);
		if ( !( $res && $res->numRows() ) ) {
			return false;
		}
		return $this->removeRelated( $row );
	}

	abstract protected function removeRelated( array $row );

	/**
	 * Used to locate the index for a query by ObjectLocator::get()
	 */
	public function getPrimaryKeyColumns() {
		return array( 'rev_id' );
	}

	protected function splitUpdate( array $row ) {
		$rev = $related = array();
		foreach ( $row as $key => $value ) {
			$prefix = substr( $key, 0, 4 );
			if ( $prefix === 'rev_' ) {
				$rev[$key] = $value;
			} else {
				$related[$key] = $value;
			}
		}
		return array( $rev, $related );
	}
}

class PostRevisionStorage extends RevisionStorage {

	public function __construct( DbFactory $dbFactory, TreeRepository $treeRepo ) {
		parent::__construct( $dbFactory );
		$this->treeRepo = $treeRepo;
	}

	public function find( array $attributes, array $options = array() ) {
		$dbr = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbr->select(
			array( 'tree' => 'flow_tree_revision', 'rev' => 'flow_revision' ),
			'*',
			$attributes,
			__METHOD__,
			$options,
			array( 'rev' => array( 'JOIN', 'tree_rev_id = rev_id' ) )
		);
		if ( !$res ) {
			return null;
		}
		$retval = array();
		foreach ( $res as $row ) {
			$retval[] = (array) $row;
		}
		return $retval;
	}

	protected function insertRelated( array $row, array $tree ) {
		$dbw = $this->dbFactory->getDB( DB_MASTER );
		$res = $dbw->insert(
			'flow_tree_revision',
			$tree,
			__METHOD__
		);
		if ( !$res ) {
			return false;
		}

		return (bool) $this->treeRepo->insert( $tree['tree_rev_descendant'], $tree['tree_parent_id'] );
	}

	protected function updateRelated( array $row, array $revisionChanges ) {
		if ( $revisionChanges ) {
			throw new \MWException( 'Update not allowed' );
		}
	}

	// this doesnt delete the whole post, it just deletes the revision.
	// The post will *always* exist in the tree structure, its just a tree
	// and we arn't going to re-parent its children;
	protected function removeRelated( array $row ) {
		return;
	}
}

class SummaryRevisionStorage extends RevisionStorage {

	public function find( array $attributes, array $options = array() ) {
		$res = $this->dbFactory->getDB( DB_MASTER )->select(
			array( 'summary' => 'flow_summary_revision', 'rev' => 'flow_revision' ),
			'*',
			$attributes,
			__METHOD__,
			$options,
			array( 'rev' => array( 'JOIN', 'summary_rev_id = rev_id' ) )
		);

		if ( !$res ) {
			return null;
		}
		$result = array();
		foreach ( $res as $row ) {
			$row = (array) $row;
			$result[$row['rev_id']] = $row;
		}
		return $result;
	}

	protected function insertRelated( array $rev, array $summary ) {
		return (bool) $this->dbFactory->getDB( DB_MASTER )->insert(
			'flow_summary_revision',
			$summary,
			__METHOD__
		);
	}

	// There is changable data in the summary half, it just points to the correct workflow
	protected function updateRelated( array $rev, array $summaryChanges ) {
		if ( $summaryChanges ) {
			throw new \MWException( 'No update allowed' );
		}
	}

	protected function removeRelated( array $row ) {
		$this->dbFactory->getDB( DB_MASTER )->delete(
			'flow_summary_revision',
			array( 'summary_rev_id' => $row['rev_id'] )
		);
	}
}

