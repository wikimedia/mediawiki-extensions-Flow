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

	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			return array( $this->getBoardLink() );
		} else {
			return array( $this->getAgentLink() );
		}
	}

	protected function getHeaderMessageKey() {
		return $this->isBundled()
			? "notification-bundle-header-{$this->type}-v2"
			: "notification-header-{$this->type}-v2";
	}

	public function getHeaderMessage() {
		$msg = $this->msg( $this->getHeaderMessageKey() );
		$msg->params( $this->getTopicTitle() );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( "notification-body-{$this->type}-v2" );
		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

}
