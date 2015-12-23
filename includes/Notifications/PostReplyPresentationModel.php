<?php

namespace Flow;


class PostReplyPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'chat';
	}

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId()
			&& $this->hasValidPostId();
	}

	public function getPrimaryLink() {
		$event = $this->isBundled() ? end( $this->getBundledEvents() ) : $this->event;
		$postId = $event->getExtraParam( 'post-id' );
		return array(
			'url' => $this->getPostLinkUrl( $postId ),
			'label' => $this->msg( 'flow-notification-link-text-view-post' )->text(),
		);
	}

	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			return array( $this->getBoardByNewestLink() );
		} else {
			return array( $this->getAgentLink() );
		}
	}

	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			list( $formattedCount, $countForPlural ) = $this->getNotificationCountForOutput();
			$msg = $this->msg( "notification-bundle-header-{$this->type}" );
			$msg->params( $formattedCount, $this->getTopicTitle() );
			return $msg;
		} else {
			$msg = parent::getHeaderMessage();
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->getTopicTitle() );
			return $msg;
		}
	}

	public function getBodyMessage() {
		if ( !$this->isBundled() ) {
			$msg = $this->msg( "notification-body-{$this->type}-v2" );
			$msg->params( $this->getContentSnippet() );
			return $msg;
		}
	}

}
