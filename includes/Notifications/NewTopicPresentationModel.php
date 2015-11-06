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
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( 'notification-body-flow-new-topic' );
		list( $formattedName, $genderName ) = $this->getAgentForOutput();
		$msg->params( $formattedName, $genderName );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		return $msg;
	}

}
