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
		if ( $this->isBundled() ) {
			$msg = $this->getMessageWithAgent( "notification-bundle-header-{$this->type}" );
			list( $formattedCount, $countForPlural ) = $this->getNotificationCountForOutput();
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $formattedCount );
			$msg->params( $countForPlural );
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
		} else {
			$msg = $this->getMessageWithAgent( "notification-body-{$this->type}" );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			return $msg;
		}
	}

}
