<?php

namespace Flow;

use EchoDiscussionParser;
use EchoEvent;
use EchoEventPresentationModel;
use Flow\Model\UUID;
use Title;

abstract class FlowPresentationModel extends EchoEventPresentationModel {

	protected function hasTitle() {
		return (bool)$this->event->getTitle();
	}

	protected function hasValidTopicWorkflowId() {
		$topicWorkflowId = $this->event->getExtraParam( 'topic-workflow' );
		return $topicWorkflowId && $topicWorkflowId instanceof UUID;
	}

	protected function hasValidPostId() {
		$postId = $this->event->getExtraParam( 'post-id' );
		return $postId && $postId instanceof UUID;
	}

	public function getSecondaryLinks() {
		return array( $this->getAgentLink() );
	}

	/**
	 * Return a full url of following format:
	 *   https://<site>/wiki/Topic:<topicId>?topic_showPostId=<$firstChronologicallyPostId>&fromnotif=1#flow-post-<$anchorPostID>
	 * @param UUID|null $firstChronologicallyPostId First unread post ID
	 * @param UUID|null $anchorPostID Post ID for anchor (i.e. to scroll to)
	 * @return string
	 */
	protected function getPostLinkUrl( $firstChronologicallyPostId = null, $anchorPostId = null ) {
		/** @var UUID $workflowId */
		$workflowId = $this->event->getExtraParam( 'topic-workflow' );
		if ( $firstChronologicallyPostId === null ) {
			/** @var UUID $firstChronologicallyPostId */
			$firstChronologicallyPostId = $this->event->getExtraParam( 'post-id' );
		}

		if ( $anchorPostId === null ) {
			$anchorPostId = $firstChronologicallyPostId;
		}

		$title = $this->getTopicTitle(
			'flow-post-' . $anchorPostId->getAlphadecimal()
		);

		$url = $title->getFullURL(
			array(
				'topic_showPostId' => $firstChronologicallyPostId->getAlphadecimal(),
				'fromnotif' => 1,
			)
		);

		return $url;
	}

	/**
	 * Return a full url of following format:
	 *   https://<site>/wiki/Topic:<topicId>&fromnotif=1
	 * @return string
	 */
	protected function getTopicLinkUrl() {
		/** @var UUID $workflowId */
		$workflowId = $this->event->getExtraParam( 'topic-workflow' );

		$url = $this->getTopicTitle()->getFullURL( array( 'fromnotif' => 1 ) );

		return $url;
	}

	/**
	 * Get the topic title Title
	 *
	 * @param string $fragment Optional fragment
	 * @return Title Topic title
	 */
	protected function getTopicTitleObj( $fragment = '' ) {
		return Title::makeTitleSafe(
			NS_TOPIC,
			$workflowId->getAlphadecimal(),
			$fragment
		);
	}

	/**
	 * Return a full url to a board sorted by newest topic
	 *   ?topiclist_sortby=newest
	 * @return string
	 */
	protected function getBoardLinkByNewestTopic() {
		return array(
			'url' => $this->getBoardByNewestTopicUrl(),
			'label' => $this->msg( 'flow-notification-link-text-view-topics' )->text()
		);
	}

	protected function getBoardByNewestTopicUrl() {
		/** @var UrlGenerator $urlGenerator */
		$urlGenerator = Container::get( 'url_generator' );
		$url = $urlGenerator->boardLink( $this->event->getTitle(), 'newest' )->getFullURL();
		return $url;
	}

	protected function getViewTopicLink() {
		$title = Title::newFromText( $this->event->getExtraParam( 'topic-workflow' )->getAlphadecimal(), NS_TOPIC );
		return array(
			'url' => $title->getFullURL(),
			'label' => $this->msg( 'flow-notification-link-text-view-topic' )->text(),
		);
	}

	protected function getBoardByNewestLink() {
		return $this->getBoardLink( 'newest' );
	}

	protected function getBoardLink( $sortBy = null ) {
		$query = $sortBy ? array( 'topiclist_sortby' => $sortBy ) : array();
		return $this->getPageLink(
			$this->event->getTitle(), '', true, $query
		);
	}

	protected function getContentSnippet() {
		return $this->event->getExtraParam( 'content' );
	}

	protected function getTopicTitle( $extraParamName = 'topic-title' ) {
		$topicTitle = $this->event->getExtraParam( $extraParamName );
		return $this->truncateTopicTitle( $topicTitle );
	}

	protected function truncateTopicTitle( $topicTitle ) {
		return $this->language->embedBidi(
			$this->language->truncate(
				$topicTitle,
				self::SECTION_TITLE_RECOMMENDED_LENGTH,
				'...',
				false
			)
		);
	}

	protected function isUserTalkPage() {
		// Would like to do $this->event->getTitle()->equals( $this->user->getTalkPage() )
		// but $this->user is private in the parent class
		$username = $this->getViewingUserForGender();
		return $this->event->getTitle()->getNamespace() === NS_USER_TALK &&
			$this->event->getTitle()->getText() === $username;
	}

	/**
	 * Get a flow-specific watch/unwatch dynamic action link
	 *
	 * @return array Array representing the dynamic action secondary link
	 */
	protected function getFlowUnwatchDynamicActionLink( Title $title, $isTopic = false ) {
		$title = $this->event->getTitle();
		$link = $this->getWatchActionLink( $title );
		$type = $isTopic ? 'topic' : 'board';

		$messageKeys = array(
			'item' => array(
				// notification-dynamic-actions-flow-board-unwatch
				// notification-dynamic-actions-flow-topic-unwatch
				'title' => $this
					->msg( 'notification-dynamic-actions-flow-' . $type . '-unwatch' )
					->params(
						$title->getPrefixedText(),
						$title->getFullURL( $query )
					)
					->parse(),
			),
			'confirmation' => array(
				// notification-dynamic-actions-flow-board-unwatch-confirmation
				// notification-dynamic-actions-flow-topic-unwatch-confirmation
				'title' => $this
					->msg( 'notification-dynamic-actions-flow-' . $type . '-unwatch-confirmation' )
					->params(
						$title->getPrefixedText(),
						$title->getFullURL( $query )
					)
					->parse(),
				// notification-dynamic-actions-flow-board-unwatch-confirmation-description
				// notification-dynamic-actions-flow-topic-unwatch-confirmation-description
				'description' => $this
					->msg( 'notification-dynamic-actions-flow-' . $type . '-unwatch-confirmation-description' )
					->params(
						$title->getPrefixedText(),
						$title->getFullURL( $query )
					)
					->parse(),
			),
		);

		// Override messages with flow-specific messages
		$link[ 'data' ][ 'messages' ] = array_replace( $link[ 'data' ][ 'messages' ], $messageKeys );

		return $link;
	}
}
