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
		$link = array(
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'notification-link-text-view-mention' )->text()
		);

		if ( $this->getType() === 'post' ) {
			// override url, link straight to that specific post
			$link['url'] = $this->getPostLinkUrl();
		}

		return false;
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

		if ( $this->getType() === 'post' ) {
			$msg->params( $this->getTopicTitle() );
		}

		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( "notification-body-{$this->type}" );
		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

	protected function getType() {
		$extra = $this->event->getExtra();

		// we didn't use to include the type to differentiate messages, but
		// then we only supported posts
		return isset( $extra['revision-type'] ) ? $extra['revision-type'] : 'post';
	}
}
