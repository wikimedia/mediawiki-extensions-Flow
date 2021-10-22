<?php

namespace Flow\Notifications;

class FlowEnabledOnTalkpagePresentationModel extends FlowPresentationModel {

	/** @inheritDoc */
	public function getIconType() {
		return 'chat';
	}

	/** @inheritDoc */
	public function canRender() {
		return $this->hasTitle();
	}

	/** @inheritDoc */
	public function getPrimaryLink() {
		return [
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'flow-notification-link-text-enabled-on-talkpage' )->text()
		];
	}

	/**
	 * All Flow notifications have the 'Agent' link except this one.
	 *
	 * @return array[]
	 */
	public function getSecondaryLinks() {
		$userTalkLink = $this->getPageLink(
			$this->event->getTitle(), '', true
		);
		return [ $userTalkLink ];
	}

	/** @inheritDoc */
	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->getTruncatedTitleText( $this->event->getTitle(), true ) );
		return $msg;
	}

}
