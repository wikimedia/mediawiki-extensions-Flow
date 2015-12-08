<?php

namespace Flow;

class MentionPresentationModel extends FlowPresentationModel {

	public function canRender() {
		return $this->hasTitle() && $this->event->getAgent();
	}

	public function getPrimaryLink() {
		return array(
			$this->getPostLinkUrl(),
			$this->msg( 'notification-link-text-view-mention' )->text()
		);
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

	public function getSecondaryLinks() {
		return array(
			array(
				'prioritized' => true,
				'label' => $this->event->getAgent()->getName(),
				'icon' => 'userAvatar',
				'url' => $this->event->getAgent()->getUserPage()->getFullUrl()
			),
		);
	}
}
