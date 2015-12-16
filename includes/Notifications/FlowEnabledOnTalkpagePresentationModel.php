<?php

namespace Flow;

class FlowEnabledOnTalkpagePresentationModel extends FlowPresentationModel {

	public function canRender() {
		return $this->hasTitle();
	}

	public function getPrimaryLink() {
		return array(
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'flow-notification-link-text-enabled-on-talkpage' )->text()
		);
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->getMessageWithAgent( 'notification-body-flow-enabled-on-talkpage' );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		return $msg;
	}

}
