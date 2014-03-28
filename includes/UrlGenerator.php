<?php

namespace Flow;

use Flow\Data\PagerPage;
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
	 * @param UUID $workflowId
	 * @return Anchor
	 */
	public function newTopicLink( Title $title = null, UUID $workflowId ) {
		return new Anchor(
			wfMessage( 'flow-topic-action-new' ),
			$this->resolveTitle( $title, $workflowId ),
			array(
				'workflow' => $workflowId->getAlphadecimal(),
				'action' => 'new-topic',
			)
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
			wfMessage( 'hist' ),
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
			wfMessage( 'hist' ),
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
	 * Show the differences between two revisions of a header
	 *
	 * When $oldRevId is null shows the differences between $revId and the revision
	 * immediatly prior.  If $oldRevId is provided shows the differences between
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
	 * immediatly prior.  If $oldRevId is provided shows the differences between
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
				'post_newRevision' => $revId->getAlphadecimal(),
			) + ( $oldRevId === null ? array() : array(
				'post_oldRevision' => $oldRevId->getAlphadecimal(),
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
			wfMessage( 'flow-something' ),
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
	 * @return Anchor
	 */
	public function boardLink( Title $title ) {
		return new Anchor( $title->getPrefixedText(), $title );
	}
}
