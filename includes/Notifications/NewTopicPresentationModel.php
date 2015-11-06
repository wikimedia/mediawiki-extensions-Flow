<?php

namespace Flow;

class NewTopicPresentationModel extends FlowPresentationModel {

	public function canRender() {
		return $this->hasTitle();
	}

	public function getPrimaryLink() {
		return array(
			'url' => $this->getBoardLinkByNewestTopic(),
			'label' => $this->msg( 'flow-notification-link-text-view-topic' )->text()
		);
	}

	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			$msg = $this->msg( "notification-bundle-header-{$this->type}" );
			list( $countForOutput, $countForPlural ) = $this->getNotificationCountForOutput();
			$msg->params( $countForOutput, $countForPlural );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			return $msg;
		} else {
			$msg = parent::getHeaderMessage();
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			return $msg;
		}
	}

	public function getBodyMessage() {
		if ( $this->isBundled() ) {
			return false;
		}

		$msg = $this->getMessageWithAgent( 'notification-body-flow-new-topic' );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		return $msg;
	}

}
