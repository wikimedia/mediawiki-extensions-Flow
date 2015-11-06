<?php

namespace Flow;

class PostEditedPresentationModel extends FlowPresentationModel {

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId()
			&& $this->hasValidPostId();
	}

	public function getPrimaryLink() {
		return array(
			$this->getPostLinkUrl(),
			$this->msg( 'flow-notification-link-text-view-post' )->text()
		);
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( 'notification-body-flow-post-edited' );
		list( $formattedName, $genderName ) = $this->getAgentForOutput();
		$msg->params( $formattedName, $genderName );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		return $msg;
	}

}
