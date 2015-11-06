<?php

namespace Flow;

use EchoEventPresentationModel;
use Flow\Model\UUID;
use Title;

class TopicRenamedPresentationModel extends EchoEventPresentationModel {

	public function canRender() {
		$validTitle = (bool)$this->event->getTitle();
		$validWorkflowId = $this->event->getExtraParam( 'topic-workflow' ) instanceof UUID;
		$validPostId = $this->event->getExtraParam( 'post-id' ) instanceof UUID;
		return $validTitle && $validWorkflowId && $validPostId;
	}

	public function getIconType() {
		return 'flow-discussion';
	}

	public function getPrimaryLink() {
		$url = $this->getPostLinkUrl();
		$text = $this->msg( 'flow-notification-link-text-view-post' )->text();
		return array( $url, $text );
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getExtraParam( 'old-subject' ) );
		$msg->params( $this->event->getExtraParam( 'new-subject' ) );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		return $msg;
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

		$title  = Title::makeTitleSafe(
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

}
