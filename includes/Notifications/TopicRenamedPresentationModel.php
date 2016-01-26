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
		return parent::getHeaderMessageKey() . '-v2';
	}

	public function getHeaderMessage() {
		$msg = $this->msg( $this->getHeaderMessageKey() );
		$msg->params( $this->getTopicTitle( 'old-subject' ) );
		$msg->params( $this->getTopicTitle( 'new-subject' ) );
		return $msg;
	}

}
