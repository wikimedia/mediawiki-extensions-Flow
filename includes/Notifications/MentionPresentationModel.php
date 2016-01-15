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

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

}
