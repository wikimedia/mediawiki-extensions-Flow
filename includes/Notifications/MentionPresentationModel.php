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

		// override url, link straight to that specific post/topic
		if ( $this->getType() === 'post' ) {
			$link['url'] = $this->getPostLinkUrl();
		} elseif ( $this->getType() === 'post-summary' ) {
			$link['url'] = $this->getTopicLinkUrl();
		}

		return $link;
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

		if ( in_array( $this->getType(), array( 'post', 'post-summary' ) ) ) {
			$msg->plaintextParams( $this->getTopicTitle() );
		}

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
		return $this->event->getExtraParam( 'revision-type', 'post' );
	}
}
