<?php

namespace Flow\Formatter;

use Flow\Data\PagerPage;
use Flow\Model\PostRevision;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;

class TopicListQuery extends AbstractQuery {
	/**
	 * @param PagerPage $page Pagination options
	 * @return FormatterRow[]
	 */
	public function getResults( PagerPage $page ) {
		$allPostIds = $this->collectPostIds( $page->getResults() );
		$posts = $this->collectRevisions( $allPostIds );

		$missing = array_diff(
			array_keys( $allPostIds ),
			array_keys( $posts )
		);
		if ( $missing ) {
			$needed = array();
			foreach ( $missing as $alpha ) {
				// convert alpha back into UUID object
				$needed[] = $allPostIds[$alpha];
			}
			$posts += $this->createFakePosts( $needed );
		}

		$this->loadMetadataBatch( $posts );
		$resutls = array();
		foreach ( $posts as $post ) {
			try {
				$row = new TopicListRow;
				$results[] = $result = $this->buildResult( $post, null, $row );
				$replyToId = $result->revision->getReplyToId();
				$replyToId = $replyToId ? $replyToId->getAlphadecimal() : null;
				$postId = $result->revision->getPostId()->getAlphadecimal();
				$replies[$replyToId] = $postId;
			} catch ( FlowException $e ) {
				\MWExceptionHandler::logException( $e );
			}
		}
		foreach ( $results as $result ) {
			$alpha = $result->revision->getPostId()->getAlphadecimal();
			$result->replies = isset( $replies[$alpha] ) ? $replies[$alpha] : array();
		}

		return $results;
	}

	/**
	 * @param TopicListEntry[] $found
	 * @return UUID[] Indexed by alphadecimal representation
	 */
	protected function collectPostIds( array $found ) {
		if ( !$found ) {
			return array();
		}

		$topicIds = array();
		foreach ( $found as $entry ) {
			$topicIds[] = $entry->getId();
		}

		// Get the full list of postId's necessary
		$nodeList = $this->treeRepository->fetchSubtreeNodeList( $topicIds );

		// Merge all the children from the various posts into one array
		if ( !$nodeList ) {
			// It should have returned at least $topicIds
			wfDebugLog( 'Flow', __METHOD__ . ': No result received from TreeRepository::fetchSubtreeNodeList' );
			$postIds = $topicIds;
		} elseif ( count( $nodeList ) === 1 ) {
			$postIds = reset( $nodeList );
		} else {
			$postIds = call_user_func_array( 'array_merge', $nodeList );
		}

		// re-index by alphadecimal id
		return array_combine(
			array_map( function( $x ) { return $x->getAlphadecimal(); }, $postIds ),
			$postIds
		);
	}

	/**
	 * @param array $postIds
	 * @return PostRevision[] Indexed by alphadecimal post id
	 */
	protected function collectRevisions( array $postIds ) {
		$queries = array();
		foreach ( $postIds as $postId ) {
			$queries[] = array( 'tree_rev_descendant_id' => $postId );
		}
		$found = $this->storage->findMulti( 'PostRevision', $queries, array(
			'sort' => 'rev_id',
			'order' => 'DESC',
			'limit' => 1,
		) );

		// index results by post id for later filtering
		$result = array();
		foreach ( $found as $row ) {
			$revision = reset( $row );
			$result[$revision->getPostId()->getAlphadecimal()] = $revision;
		}

		return $result;
	}

	/**
	 * @param PostRevision[] $posts
	 * @param UUID[] $missing
	 */
	protected function createFakePosts( array $missing ) {
		$parents = $this->treeRepo->fetchParentMap( $missing );
		$posts = array();
		foreach ( $missing as $uuid ) {
			$alpha = $uuid->getAlphadecimal();
			if ( !isset( $parents[$alpha] ) ) {
				wfDebugLog( 'Flow', __METHOD__ . ": Unable not locate parent for postid $alpha" );
				continue;
			}
			$content = wfMessage( 'flow-stub-post-content' )->text();
			$username = wfMessage( 'flow-system-usertext' )->text();
			$user = \User::newFromName( $username );

			// create a stub post instead of failing completely
			$post = PostRevision::newFromId( $uuid, $user, $content );
			$post->setReplyToId( $parents[$alpha] );
			$posts[$alpha] = $post;
		}

		return $posts;
	}
}

class TopicListRow extends FormatterRow {
	public $replies;
}
