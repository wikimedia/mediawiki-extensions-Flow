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
		return parent::getHeaderMessageKey() . '-' . $this->getType();
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->getTruncatedTitleText( $this->event->getTitle(), true) );
		$msg->params( $this->getViewingUserForGender() );
		$msg->params( $this->getTopicTitle() );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( "notification-body-{$this->type}" );
		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

	protected function getType() {
		// we didn't use to include the type to differentiate messages, but
		// then we only supported posts
		return $this->event->getExtraParam( 'type', 'post' );
	}
}
