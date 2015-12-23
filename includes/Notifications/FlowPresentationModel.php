<?php

namespace Flow;

use EchoDiscussionParser;
use EchoEvent;
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

	public function getSecondaryLinks() {
		return array( $this->getAgentLink() );
	}

	/**
	 * Return a full url of following format:
	 *   https://<site>/wiki/Topic:<topicId>?topic_showPostId=<postId>&fromnotif=1#flow-post-<postId>
	 * @todo: Generate a url to the first unread post of a topic when we figure out bundling in the new email formatter.
	 * @param UUID|null $postId
	 * @return string
	 */
	protected function getPostLinkUrl( $postId = null ) {
		/** @var UUID $workflowId */
		$workflowId = $this->event->getExtraParam( 'topic-workflow' );
		if ( $postId === null ) {
			/** @var UUID $postId */
			$postId = $this->event->getExtraParam( 'post-id' );
		}

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

	public static function getEventUser( EchoEvent $event ) {
		$agent = $event->getAgent();
		return $agent->isAnon() ? $agent->getName() : $agent->getId();
	}

	protected function getOtherAgentsCountForOutput() {
		return $this->getNotificationCountForOutput( false, array( $this, 'getEventUser' ));
	}

	protected function getViewTopicLink() {
		$title = Title::newFromText( $this->event->getExtraParam( 'topic-workflow' )->getAlphadecimal(), NS_TOPIC );
		return array(
			'url' => $title->getFullURL(),
			'label' => $this->msg( 'flow-notification-link-text-view-topic' )->text(),
		);
	}

	protected function getBoardByNewestLink() {
		return array(
			'label' => $this->event->getTitle()->getPrefixedText(),
			'url' => $this->getBoardByNewestTopicUrl(),
			'prioritized' => true,
			'icon' => 'speechBubbles',
			'description' => null,
		);
	}

	protected function getBoardLink() {
		return array(
			'label' => $this->event->getTitle()->getPrefixedText(),
			'url' => $this->event->getTitle()->getFullURL(),
			'prioritized' => true,
			'icon' => 'speechBubbles',
			'description' => null,
		);
	}

	protected function getContentSnippet() {
		return EchoDiscussionParser::getTextSnippet(
			$this->event->getExtraParam( 'content' ),
			$this->language,
			30
		);
	}

	protected function getTopicTitle() {
		return wfEscapeWikiText( $this->event->getExtraParam( 'topic-title' ) );
	}
}
