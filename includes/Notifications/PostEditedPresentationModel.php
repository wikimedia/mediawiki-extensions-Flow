<?php

namespace Flow;

class PostEditedPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'flow-post-edited';
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
		list( $formattedCount, $countForPlural ) = $this->getOtherAgentsCountForOutput();
		if ( $countForPlural > 0 ) {
			$msg = $this->getMessageWithAgent( "notification-bundle-header-{$this->type}" );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $formattedCount, $countForPlural );
		} else {
			$msg = parent::getHeaderMessage();
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
		}
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

	public function getBodyMessage() {
		if ( $this->isBundled() ) {
			return false;
		} else {
			$msg = $this->getMessageWithAgent( "notification-body-{$this->type}" );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->event->getExtraParam( 'topic-title' ) );
			$msg->params( $this->getViewingUserForGender() );
			return $msg;
		}
	}

}
