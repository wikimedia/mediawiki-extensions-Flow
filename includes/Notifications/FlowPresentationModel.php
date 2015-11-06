<?php

namespace Flow;

use EchoEventPresentationModel;
use Flow\Model\UUID;
use Title;

abstract class FlowPresentationModel extends EchoEventPresentationModel {

	public function getIconType() {
		return 'flow-discussion';
	}

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

	/**
	 * Return a full url of following format:
	 *   https://<site>/wiki/Topic:<topicId>?topic_showPostId=<postId>&fromnotif=1#flow-post-<postId>
	 * @todo: Generate a url to the first unread post of a topic when we figure out bundling in the new email formatter.
	 * @return string
	 */
	protected function getPostLinkUrl() {
		/** @var UUID $workflowId */
		$workflowId = $this->event->getExtraParam( 'topic-workflow' );
		/** @var UUID $postId */
		$postId = $this->event->getExtraParam( 'post-id' );

		$title = Title::makeTitleSafe(
			NS_TOPIC,
			$workflowId->getAlphadecimal(),
			'flow-post-' . $postId->getAlphadecimal()
		);

		$url = $title->getFullURL(
			array(
				'topic_showPostId' => $postId->getAlphadecimal(),
				'fromnotif' => 1,
			)
		);

		return $url;
	}

	/**
	 * Return a full url to a board sorted by newest topic
	 *   ?topiclist_sortby=newest
	 * @return string
	 */
	protected function getBoardLinkByNewestTopic() {
		/** @var UrlGenerator $urlGenerator */
		$urlGenerator = Container::get( 'url_generator' );
		return $urlGenerator->boardLink( $this->event->getTitle(), 'newest' )->getFullURL();
	}

	protected function isBundled() {
		return $this->getBundleCount() > 1;
	}

	/**
	 * @param int|null $cap Maximum number to return or null for no maximum
	 * @return int Number of bundle events, potentially capped to $cap
	 */
	protected function getBundleCount( $cap = null ) {
		$count = count( $this->getBundledEvents() );
		if ( is_numeric( $cap ) ) {
			return max( array( $count, $cap ) );
		}
		return $count;
	}

	protected function getNotificationCountForOutput() {
		global $wgEchoMaxNotificationCount;
		$count = count( $this->getBundledEvents() ) - 1;
		if ( $count > $wgEchoMaxNotificationCount ) {
			return array(
				$this->msg( 'echo-notification-count' )->numParams( $wgEchoMaxNotificationCount )->text(),
				$wgEchoMaxNotificationCount
			);
		} else {
			return array(
				$count,
				$count
			);
		}
	}

}
