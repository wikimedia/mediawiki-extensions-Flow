<?php

namespace Flow;

class NewTopicPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return 'flow-new-topic';
	}

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId();
	}

	public function getPrimaryLink() {
		if ( $this->isBundled() ) {
			return $this->getBoardLinkByNewestTopic();
		} else {
			return $this->getViewTopicLink();
		}
	}

	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			return array();
		} else {
			return array(
				$this->getAgentLink(),
				$this->getBoardByNewestLink(),
			);
		}
	}

	public function getBodyMessage() {
		if ( $this->isBundled() ) {
			return false;
		} elseif ( $this->isUserTalkPage() ) {
			$msg = $this->msg( "notification-body-{$this->type}-user-talk" );
		} else {
			$msg = $this->msg( "notification-body-{$this->type}-v2" );
		}

		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

	protected function getHeaderMessageKey() {
		if ( $this->isUserTalkPage() ) {
			return parent::getHeaderMessageKey() . '-user-talk';
		}

		return parent::getHeaderMessageKey() . '-v2';
	}

	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			$msg = $this->msg( "notification-bundle-header-{$this->type}" );
			list( $countForOutput, $countForPlural ) = $this->getNotificationCountForOutput();
			$msg->params( $countForOutput, $countForPlural );
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			return $msg;
		} else {
			$msg = parent::getHeaderMessage();
			$msg->params( $this->event->getTitle()->getPrefixedText() );
			$msg->params( $this->getTopicTitle() );
			return $msg;
		}
	}
}
