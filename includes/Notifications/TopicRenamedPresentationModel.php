<?php

namespace Flow;


class TopicRenamedPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'flow-topic-renamed';
	}

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId();
	}

	public function getPrimaryLink() {
		return $this->getViewTopicLink();
	}

	public function getSecondaryLinks() {
		return array(
			$this->getAgentLink(),
			$this->getBoardByNewestLink(),
		);
	}

	protected function getHeaderMessageKey() {
		if ( $this->isUserTalkPage() ) {
			return parent::getHeaderMessageKey() . '-user-talk';
		} else {
			return parent::getHeaderMessageKey() . '-v2';
		}
	}

	public function getHeaderMessage() {
		$msg = $this->msg( $this->getHeaderMessageKey() );
		$msg->plaintextParams( $this->getTopicTitle( 'old-subject' ) );
		$msg->plaintextParams( $this->getTopicTitle( 'new-subject' ) );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

}
