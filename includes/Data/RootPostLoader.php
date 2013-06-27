<?php

namespace Flow\Data;

use Flow\Block\TopicBlock;
use Flow\Repository\TreeRepository;

/**
 * I'm pretty sure this will work for any subtree, not just the topic
 * root.  Probably needs to be renamed
 */
class RootPostLoader {
	public function __construct( ManagerGroup $storage, TreeRepository $treeRepo ) {
		$this->storage = $storage;
		$this->treeRepo = $treeRepo;
	}

	public function get( $topicId ) {
		$result = $this->getMulti( array( $topicId ) );
		return reset( $result );
	}

	public function getMulti( array $topicIds ) {
		// load posts for all located post ids
		$queries = array();
		foreach( $this->getDescendantIds( $topicIds ) as $postId ) {
			$queries[] = array( 'tree_rev_descendant' => $postId );
		}
		$found = $this->storage->findMulti( 'PostRevision', $queries, array(
			'sort' => 'rev_id',
			'order' => 'DESC',
			'limit' => 1,
		) );
		$posts = $children = array();
		foreach ( $found as $indexResult ) {
			$post = reset( $indexResult ); // limit => 1 means only 1 result
			$posts[$post->getPostId()] = $post;
			if ( $post->getReplyToId() ) {
				$children[$post->getReplyToId()][] = $post;
			}
		}

		// link parents to their children
		foreach ( $posts as $postId => $post ) {
			if ( isset( $children[$postId] ) ) {
				$post->setChildren( $children[$postId] );
			} else {
				$post->setChildren( array() );
			}
		}

		// return only the requested posts, rest are available as children
		$roots = array();
		foreach ( $topicIds as $id ) {
			$roots[$id] = $posts[$id];
		}
		return $roots;
	}

	protected function getDescendantIds( array $postIds ) {
		// list of all posts descendant from the provided $postIds
		$nodeList = $this->treeRepo->fetchSubtreeNodeList( $postIds );
		// merge all the children into one array
		if ( !$nodeList ) {
			return array(); // nothing?
		} elseif( count( $nodeList ) === 1 ) {
			$merged = reset( $nodeList );
		} else {
			$merged = call_user_func_array( 'array_merge', $nodeList );
		}
		// Add all the parents as well( which means func needs a new name )
		return array_merge( array_keys( $nodeList ), $merged );
	}
}
