<?php

namespace Flow\Data;

use Flow\Block\TopicBlock;
use Flow\Model\Workflow;
use Flow\Model\UUID;
use Flow\Repository\TreeRepository;

/**
 * I'm pretty sure this will generally work for any subtree, not just the topic
 * root.  The problem is once you allow any subtree you need to handle things like
 */
class RootPostLoader {
	public function __construct( ManagerGroup $storage, TreeRepository $treeRepo ) {
		$this->storage = $storage;
		$this->treeRepo = $treeRepo;
	}

	public function get( Workflow $workflow ) {
		$result = $this->getMulti( array( $workflow ) );
		return reset( $result );
	}

	public function getMulti( array $workflows ) {
		if ( !$workflows ) {
			return array();
		}
		$topicIds = array();
		foreach ( $workflows as $idx => $workflow ) {
			$topicIds[] = $workflow->getId();
			// re-index by hexid
			unset( $workflows[$idx] );
			$workflows[$workflow->getId()->getHex()] = $workflow;
		}
		// load posts for all located post ids
		$allPostIds =  $this->fetchRelatedPostIds( $topicIds );
		$queries = array();
		foreach ( $allPostIds as $postId ) {
			$queries[] = array( 'tree_rev_descendant_id' => $postId );
		}
		$found = $this->storage->findMulti( 'PostRevision', $queries, array(
			'sort' => 'rev_id',
			'order' => 'DESC',
			'limit' => 1,
		) );
		$posts = $children = array();
		foreach ( $found as $indexResult ) {
			$post = reset( $indexResult ); // limit => 1 means only 1 result per query
			if ( isset( $posts[$post->getPostId()->getHex()] ) ) {
				throw new \MWException( 'Multiple results for id: ' . $post->getPostId()->getHex() );
			}
			$posts[$post->getPostId()->getHex()] = $post;
			if ( $post->getReplyToId() ) {
				$children[$post->getReplyToId()->getHex()][] = $post;
			}
		}
		$prettyPostIds = array();
		foreach ( $allPostIds as $id ) {
			$prettyPostIds[] = $id->getHex();
		}
		$missing = array_diff( $prettyPostIds, array_keys( $posts ) );
		if ( $missing ) {
			// TODO: fake up a pseudo-post to hold the children? At this point in
			// dev its probably a bug we want to see.
			throw new \MWException( 'Missing Posts: ' . json_encode( $missing ) );
		}
		// another helper to catch bugs in dev
		$extra = array_diff( array_keys( $posts ), $prettyPostIds );
		if ( $extra ) {
			throw new \MWException( 'Found unrequested posts: ' . json_encode( $extra ) );
		}
		$extraParents = array_diff( array_keys( $children ), $prettyPostIds );
		if ( $extraParents ) {
			throw new \MWException( 'Found posts with unrequested parents: ' . json_encode( $extraParents ) );
		}

		foreach ( $posts as $postId => $post ) {
			$postChildren = array();
			$postDepth = 0;

			// link parents to their children
			if ( isset( $children[$postId] ) ) {
				// sort children with oldest items first
				usort( $children[$postId], function( $a, $b ) {
					return $b->compareCreateTime( $a );
				} );
				$postChildren = $children[$postId];
			}

			// determine threading depth of post
			$replyToId = $post->getReplyToId();
			while ( $replyToId && isset( $children[$replyToId->getHex()] ) ) {
				$postDepth++;
				$replyToId = $posts[$replyToId->getHex()]->getReplyToId();
			}

			$post->setChildren( $postChildren );
			$post->setDepth( $postDepth );
			foreach ( $postChildren as $child ) {
				$child->setParent( $post );
			}
		}

		// return only the requested posts, rest are available as children.
		// Return in same order as requested
		$roots = array();
		foreach ( $topicIds as $id ) {
			$roots[$id->getHex()] = $posts[$id->getHex()];
			$roots[$id->getHex()]->setWorkflow( $workflow );
		}

		return $roots;
	}

	protected function fetchRelatedPostIds( array $postIds ) {
		// list of all posts descendant from the provided $postIds
		$nodeList = $this->treeRepo->fetchSubtreeNodeList( $postIds );
		// merge all the children from the various posts into one array
		if ( !$nodeList ) {
			// It should have returned at least $postIds
			// TODO: log errors?
			$res = $postIds;
		} elseif( count( $nodeList ) === 1 ) {
			$res = reset( $nodeList );
		} else {
			$res = call_user_func_array( 'array_merge', $nodeList );
		}

		$retval = array();
		foreach ( $res as $id ) {
			$retval[$id->getHex()] = $id;
		}
		return $retval;
	}
}
