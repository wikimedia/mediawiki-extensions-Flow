<?php

namespace Flow;


class PostReplyPresentationModel extends FlowPresentationModel {

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
		if ( $this->isBundled() ) {
			$msg = $this->getMessageWithAgent( "notification-bundle-body-{$this->type}" );
			list( $formattedCount, $countForPlural ) = $this->getNotificationCountForOutput( false );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			$msg->params( $formattedCount, $countForPlural );
			return $msg;
		} else {
			$msg = $this->getMessageWithAgent( "notification-body-{$this->type}" );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			return $msg;
		}
	}

}
