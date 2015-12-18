<?php

namespace Flow;


class TopicRenamedPresentationModel extends FlowPresentationModel {

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId()
			&& $this->hasValidPostId();
	}

	public function getPrimaryLink() {
		return array(
			'url' => $this->getPostLinkUrl(),
			'label' => $this->msg( 'flow-notification-link-text-view-topic' )->text()
		);
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getExtraParam( 'old-subject' ) );
		$msg->params( $this->event->getExtraParam( 'new-subject' ) );
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		return $msg;
	}

}
