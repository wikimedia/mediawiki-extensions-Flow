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
		return parent::getHeaderMessageKey() . '-v2';
	}

	public function getHeaderMessage() {
		$key = $this->isBundled() ? "notification-bundle-header-{$this->type}-v2" : $this->getHeaderMessageKey();
		$msg = $this->msg( $key );
		$msg->params( $this->getTopicTitle() );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( "notification-body-{$this->type}-v2" );
		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

}
