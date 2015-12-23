<?php

namespace Flow;

class MentionPresentationModel extends FlowPresentationModel {

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

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( "notification-body-{$this->type}" );
		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

}
