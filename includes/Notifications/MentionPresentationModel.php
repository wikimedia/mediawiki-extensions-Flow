<?php

namespace Flow;

class MentionPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'mention';
	}

	public function canRender() {
		return $this->hasTitle();
	}

	public function getPrimaryLink() {
		return array(
			'url' => $this->getPostLinkUrl(),
			'label' => $this->msg( 'notification-link-text-view-mention' )->text()
		);
	}

	public function getSecondaryLinks() {
		return array(
			$this->getAgentLink(),
			$this->getBoardByNewestLink(),
		);
	}

	public function getHeaderMessageKey() {
		$extra = $this->event->getExtra();

		// we didn't use to include the type to differentiate messages, but
		// then we only supported posts
		$type = isset( $extra['revision-type'] ) ? $extra['revision-type'] : 'post';

		// PostRevision is used for both topic & post, but we'll want to
		// differentiate...
		if ( $type === 'post' && $extra['post-id'] === $extra['topic-title'] ) {
			$type = 'topic';
		}

		return parent::getHeaderMessageKey() . '-' . $type;
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->getTopicTitle() );
		$msg->params( $this->getTruncatedTitleText( $this->event->getTitle(), true) );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( "notification-body-{$this->type}" );
		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

}
