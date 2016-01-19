<?php

namespace Flow;

class FlowEnabledOnTalkpagePresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'chat';
	}

	public function canRender() {
		return $this->hasTitle();
	}

	public function getPrimaryLink() {
		return array(
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'flow-notification-link-text-enabled-on-talkpage' )->text()
		);
	}

	/**
	 * All Flow notifications have the 'Agent' link except this one.
	 *
	 * @return array Empty array
	 */
	public function getSecondaryLinks() {
		return array();
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
