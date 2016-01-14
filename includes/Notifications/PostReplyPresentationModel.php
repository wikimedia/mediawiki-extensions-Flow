<?php

namespace Flow;


class PostReplyPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'flow-post-reply';
	}

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId()
			&& $this->hasValidPostId();
	}

	public function getPrimaryLink() {
		return array(
			'url' => $this->getPostLinkUrl(),
			'label' => $this->msg( 'flow-notification-link-text-view-post' )->text()
		);
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getTitle()->getPrefixedText() );
		$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		return $msg;
	}

	public function getBodyMessage() {
		list( $formattedCount, $countForPlural ) = $this->getOtherAgentsCountForOutput();
		if ( $countForPlural > 0 ) {
			$msg = $this->getMessageWithAgent( "notification-bundle-body-{$this->type}" );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
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
