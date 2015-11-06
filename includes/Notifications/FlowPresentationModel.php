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

	protected function getBoardLinkByNewestTopic() {
		/** @var UrlGenerator $urlGenerator */
		$urlGenerator = Container::get( 'url_generator' );
		return $urlGenerator->boardLink( $this->event->getTitle(), 'newest' )->getFullURL();
	}

}
