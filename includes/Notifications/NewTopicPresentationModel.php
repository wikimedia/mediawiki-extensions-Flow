<?php

namespace Flow;

class NewTopicPresentationModel extends FlowPresentationModel {

	public function canRender() {
		return $this->hasTitle();
	}

	public function getPrimaryLink() {
		return array(
			$this->getBoardLinkByNewestTopic(),
			$this->msg( 'flow-notification-link-text-view-topic' )->text()
		);
	}

	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			$msg = $this->msg( "notification-bundle-header-{$this->type}" );
			$msg->params( $this->getBundleCount( 250 ) );
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
