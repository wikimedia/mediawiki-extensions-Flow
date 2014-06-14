<?php

namespace Flow;

use Flow\Data\PagerPage;
use Flow\Model\AbstractRevision;
use Flow\Model\UUID;
use Title;

class UrlGenerator extends BaseUrlGenerator {

	/**
	 * Reverse pagination on a topic list.
	 *
	 * @var Title|null $title Page the workflow is on, resolved from workflowId when null
	 * @var UUID $workflowId The topic list being paginated
	 * @var PagerPage $page Pagination information
	 * @var string $direction 'fwd' or 'rev'
	 * @return Anchor|null
	 */
	public function paginateTopicsLink( Title $title = null, UUID $workflowId, PagerPage $page, $direction ) {
		$linkData = $page->getPagingLink( $direction );
		if ( !$linkData ) {
			return null;
		}
		return new Anchor(
			wfMessage( "flow-paging-$direction" ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'topiclist_offset-id' => $linkData['offset'],
				'topiclist_offset-dir' => $direction,
				'topiclist_limit' => $linkData['limit'],
			)
		);
	}

	/**
	 * Link to create new topic on a topiclist.
	 *
	 * @param Title|null $title
	 * @param UUID|null $workflowId
	 * @return Anchor
	 */
	public function newTopicLink( Title $title = null, UUID $workflowId = null ) {
		$query = array( 'action' => 'new-topic' );
		if ( $workflowId ) {
			$query['workflow'] = $workflowId->getAlphadecimal();
		}

		return new Anchor(
			wfMessage( 'flow-topic-action-new' ),
			$this->resolveTitle( $title, $workflowId ),
			$query
		);
	}

	/**
	 * Reply to an individual post in a topic workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function replyPostLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-post-action-reply' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_replyTo' => $postId->getAlphadecimal(),
				'action' => 'reply',
			)
		);
	}

	/**.
	 * Edit the header at the specified workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function editHeaderLink( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-edit-header' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'action' => 'edit-header',
			)
		);
	}

	/**
	 * Edit the title of a topic workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function editTitleLink( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-edit-title' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'action' => 'edit-title',
			)
		);
	}

	public function editPostLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-edit-post' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'edit-post',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
			)
		);
	}
	/**
	 * View a specific revision of a header workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @return Anchor
	 */
	public function headerRevisionLink( Title $title = null, UUID $workflowId, UUID $revId ) {
		return new Anchor(
			wfMessage( 'flow-link-header-revision' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'header_revId' => $revId->getAlphadecimal(),
			)
		);
	}

	/**
	 * View a specific revision of a topic title
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @return Anchor
	 */
	public function topicRevisionLink( Title $title = null, UUID $workflowId, UUID $revId ) {
		return new Anchor(
			wfMessage( 'flow-link-topic-revision' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_revId' => $revId->getAlphadecimal(),
			)
		);
	}

	/**
	 * View a specific revision of a post within a topic workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @param UUID $revId
	 * @return Anchor
	 */
	public function postRevisionLink( Title $title = null, UUID $workflowId, UUID $postId, UUID $revId ) {
		return new Anchor(
			wfMessage( 'flow-link-post-revision' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
				'topic_revId' => $revId->getAlphadecimal(),
			),
			// necessary?
			'#flow-post-' . $postId->getAlphadecimal()
		);
	}

	/**
	 * View the topic at the specified workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function topicLink( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-link-topic' ),
			$this->resolveTitle( $title, $workflowId ),
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);
	}

	/**
	 * View a topic scrolled down to the provided post at the
	 * specified workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function postLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-link-post' ),
			$this->resolveTitle( $title, $workflowId ),
			array( 'workflow' => $workflowId->getAlphadecimal() ),
			'#flow-post-' . $postId->getAlphadecimal()
		);
	}

	/**
	 * Show the history of a specific post within a topic workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function postHistoryLink( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-post-action-post-history' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'history',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
			)
		);
	}

	/**
	 * Show the history of a workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function workflowHistoryLink( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-history' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'action' => 'history',
			)
		);
	}

	/**
	 * Show the history of a flow board.
	 *
	 * @param Title $title
	 * @return Anchor
	 */
	public function boardHistoryLink( Title $title ) {
		return new Anchor(
			wfMessage( 'hist' ),
			$title,
			array( 'action' => 'history' )
		);
	}

	/**
	 * Show the differences between two revisions of a header.
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediately prior.  If $oldRevId is provided shows the differences between
	 * $oldRevId and $revId.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @param UUID|null $oldRevId
	 * @return Anchor
	 */
	public function diffHeaderLink( Title $title = null, UUID $workflowId, UUID $revId, UUID $oldRevId = null ) {
		return new Anchor(
			wfMessage( 'diff' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'compare-header-revisions',
				'workflow' => $workflowId->getAlphadecimal(),
				'header_newRevision' => $revId->getAlphadecimal(),
			) + ( $oldRevId === null ? array() : array(
				'header_oldRevision' => $oldRevId->getAlphadecimal(),
			) )
		);
	}

	/**
	 * Show the differences between two revisions of a post.
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediately prior.  If $oldRevId is provided shows the differences between
	 * $oldRevId and $revId.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @param UUID|null $oldRevId
	 * @return Anchor
	 */
	public function diffPostLink( Title $title = null, UUID $workflowId, UUID $revId, UUID $oldRevId = null ) {
		return new Anchor(
			wfMessage( 'diff' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'compare-post-revisions',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_newRevision' => $revId->getAlphadecimal(),
			) + ( $oldRevId === null ? array() : array(
				'topic_oldRevision' => $oldRevId->getAlphadecimal(),
			) )
		);
	}

	/**
	 * Show the differences between two revisions of a summary.
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediately prior.  If $oldRevId is provided shows the differences between
	 * $oldRevId and $revId.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @param UUID|null $oldRevId
	 * @return Anchor
	 */
	public function diffSummaryLink( Title $title = null, UUID $workflowId, UUID $revId, UUID $oldRevId = null ) {
		return new Anchor(
			wfMessage( 'diff' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'compare-postsummary-revisions',
				'workflow' => $workflowId->getAlphadecimal(),
				'topicsummary_newRevision' => $revId->getAlphadecimal(),
			) + ( $oldRevId === null ? array() : array(
				'topicsummary_oldRevision' => $oldRevId->getAlphadecimal(),
			) )
		);
	}

	/**
	 * View the specified workflow.
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function workflowLink( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-workflow' ),
			$this->resolveTitle( $title, $workflowId ),
			array( 'workflow' => $workflowId->getAlphadecimal() )
		);
	}

	/**
	 * View the flow board at the specified title
	 *
	 * Makes the assumption the title is flow-enabled.
	 *
	 * @param Title $title
	 * @param string $sortBy
	 * @return Anchor
	 */
	public function boardLink( Title $title, $sortBy = null ) {
		$options = array();

		if ( $sortBy ) {
			$options['topiclist_sortby'] = $sortBy;
		}

		return new Anchor(
			$title->getPrefixedText(),
			$title,
			$options
		);
	}

	public function replyAction( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-reply-link' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'reply',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal()
			)
		);
	}

	/**
	 * Edit the specified topic summary
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function editTopicSummaryAction( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-summarize-topic-submit' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'edit-topic-summary',
				'workflow' => $workflowId->getAlphadecimal()
			)
		);
	}

	/**
	 * Close the specified topic
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function closeTopicAction( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-close-topic' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'close-open-topic',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_moderationState' => 'close',
			)
		);
	}

	/**
	 * Create a header for the specified page
	 *
	 * @param Title $title
	 * @return Anchor
	 */
	public function createHeaderAction( Title $title ) {
		return new Anchor(
			wfMessage( 'flow-edit-header-link' ),
			$title,
			array( 'action' => 'edit-header' )
		);
	}

	/**
	 * Edit the specified header
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $revId
	 * @return Anchor
	 */
	public function editHeaderAction( Title $title = null, UUID $workflowId, UUID $revId ) {
		return new Anchor(
			wfMessage( 'flow-edit-header-link' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'edit-header',
				'workflow' => $workflowId->getAlphadecimal()
			)
		);
	}

	/**
	 * Edit the specified topic title
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @param UUID $revId
	 * @return Anchor
	 */
	public function editTitleAction( Title $title = null, UUID $workflowId, UUID $postId, UUID $revId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-edit-title' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'edit-title',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_revId' => $revId->getAlphadecimal(),
			)
		);
	}

	/**
	 * Edit the specified post within the specified workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @param UUID $revId
	 */
	public function editPostAction( Title $title = null, UUID $workflowId, UUID $postId, UUID $revId ) {
		return new Anchor(
			wfMessage( 'flow-post-action-edit-post' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'edit-post',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
				'topic_revId' => $revId->getAlphadecimal(),
			)
		);
	}

	/**
	 * Hide the specified topic
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function hideTopicAction( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-hide-topic' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'moderate-topic',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_moderationState' => AbstractRevision::MODERATED_HIDDEN,
			)
		);
	}

	/**
	 * Hide the specified post within the specified workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function hidePostAction( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-post-action-hide-post' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'moderate-post',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
				'topic_moderationState' => AbstractRevision::MODERATED_HIDDEN,
			)
		);
	}

	/**
	 * Delete the specified topic workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function deleteTopicAction( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-delete-topic' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'moderate-topic',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_moderationState' => AbstractRevision::MODERATED_DELETED,
			)
		);
	}

	/**
	 * Delete the specified post within the specified workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function deletePostAction( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-post-action-delete-post' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'moderate-post',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
				'topic_moderationState' => AbstractRevision::MODERATED_DELETED,
			)
		);
	}

	/**
	 * Suppress the specified topic workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function suppressTopicAction( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-suppress-topic' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'moderate-topic',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_moderationState' => AbstractRevision::MODERATED_SUPPRESSED,
			)
		);
	}

	/**
	 * Suppress the specified post within the specified workflow
	 *
	 * @param Title|null $title
	 * @param UUID $workflowId
	 * @param UUID $postId
	 * @return Anchor
	 */
	public function suppressPostAction( Title $title = null, UUID $workflowId, UUID $postId ) {
		return new Anchor(
			wfMessage( 'flow-post-action-suppress-post' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'moderate-post',
				'workflow' => $workflowId->getAlphadecimal(),
				'topic_postId' => $postId->getAlphadecimal(),
				'topic_moderationState' => AbstractRevision::MODERATED_SUPPRESSED,
			)
		);
	}

	public function newTopicAction( Title $title = null, UUID $workflowId = null ) {
		return new Anchor(
			wfMessage( 'flow-newtopic-start-placeholder' ),
			// resolveTitle doesn't accept null uuid
			$this->resolveTitle( $title, $workflowId ),
			array(
				'action' => 'new-topic'
			) + ( $workflowId === null ? array() : array(
				'workflow' => $workflowId->getAlphadecimal(),
			) )
		);
	}
}
