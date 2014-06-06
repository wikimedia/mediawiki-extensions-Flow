<?php

namespace Flow\Formatter;

use Flow\Data\PagerPage;
use Flow\Model\PostRevision;
use Flow\Model\PostSummary;
use Flow\Model\TopicListEntry;
use Flow\Model\UUID;

class TopicListQuery extends AbstractQuery {
	/**
	 * @param TopicListEntry[] $topicRevisions
	 * @return FormatterRow[]
	 */
	public function getResults( array $topicRevisions ) {
		$section = new \ProfileSection( __METHOD__ );
		$topicIds = $this->getTopicIds( $topicRevisions );
		$allPostIds = $this->collectPostIds( $topicIds );
		$topicSummary = $this->collectSummary( $topicIds );
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
		$results = array();
		foreach ( $posts as $post ) {
			try {
				$results[] = $row = new TopicRow;
				$this->buildResult( $post, null, $row );
				$replyToId = $row->revision->getReplyToId();
				$replyToId = $replyToId ? $replyToId->getAlphadecimal() : null;
				$postId = $row->revision->getPostId()->getAlphadecimal();
				$replies[$replyToId] = $postId;
				// Attach the summary
				if ( $post->isTopicTitle() && isset( $topicSummary[$postId] ) ) {
					$row->summary = $topicSummary[$postId];
				}
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
	 * @param TopicListEntry[] $topicListEntries
	 * @return UUID[]
	 */
	protected function getTopicIds( array $topicListEntires ) {
		$topicIds = array();
		foreach ( $topicListEntires as $entry ) {
			if ( $entry instanceof UUID ) {
				$topicIds[] = $entry;
			} elseif ( $entry instanceof TopicListEntry ) {
				$topicIds[] = $entry->getId();
			}
		}
		return $topicIds;
	}

	/**
	 * @param UUID[] $topicIds
	 * @return UUID[] Indexed by alphadecimal representation
	 */
	protected function collectPostIds( array $topicIds ) {
		if ( !$topicIds ) {
			return array();
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
	 * @param UUID[]
	 * @return PostSummary[]
	 */
	protected function collectSummary( $topicIds ) {
		if ( !$topicIds ) {
			return array();
		}
		$conds = array();
		foreach ( $topicIds as $topicId ) {
			$conds[] = array( 'rev_type_id' => $topicId );
		}
		$found = $this->storage->findMulti( 'PostSummary', $conds, array(
			'sort' => 'rev_id',
			'order' => 'DESC',
			'limit' => 1,
		) );
		$result = array();
		foreach ( $found as $row ) {
			$summary = reset( $row );
			$result[$summary->getSummaryTargetId()->getAlphadecimal()] = $summary;
		}
		return $result;
	}

	/**
	 * @param array $postIds
	 * @return PostRevision[] Indexed by alphadecimal post id
	 */
	protected function collectRevisions( array $postIds ) {
		$queries = array();
		foreach ( $postIds as $postId ) {
			$queries[] = array( 'rev_type_id' => $postId );
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
