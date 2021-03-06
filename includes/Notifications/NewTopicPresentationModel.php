<?php

namespace Flow\Notifications;

class NewTopicPresentationModel extends FlowPresentationModel {

	/** @inheritDoc */
	public function getIconType() {
		return $this->getType();
	}

	/** @inheritDoc */
	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId();
	}

	/** @inheritDoc */
	public function getPrimaryLink() {
		if ( $this->isBundled() ) {
			return $this->getBoardLinkByNewestTopic();
		} else {
			return $this->getViewTopicLink();
		}
	}

	/** @inheritDoc */
	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			return [
				$this->getFlowUnwatchDynamicActionLink()
			];
		} else {
			return [
				$this->getAgentLink(),
				$this->getBoardByNewestLink(),
				$this->getFlowUnwatchDynamicActionLink()
			];
		}
	}

	/** @inheritDoc */
	public function getBodyMessage() {
		if ( $this->isBundled() ) {
			return false;
		} elseif ( $this->isUserTalkPage() ) {
			$msg = $this->msg( "notification-body-flow-new-topic-user-talk" );
		} else {
			$msg = $this->msg( "notification-body-flow-new-topic-v2" );
		}

		$msg->plaintextParams( $this->getContentSnippet() );
		return $msg;
	}

	/** @inheritDoc */
	protected function getHeaderMessageKey() {
		if ( $this->isBundled() ) {
			if ( $this->isUserTalkPage() ) {
				return 'notification-bundle-header-flow-new-topic-user-talk';
			} else {
				return 'notification-bundle-header-flow-new-topic';
			}
		} else {
			if ( $this->isUserTalkPage() ) {
				return 'notification-header-flow-new-topic-user-talk';
			} else {
				return 'notification-header-flow-new-topic-v2';
			}
		}
	}

	/** @inheritDoc */
	public function getHeaderMessage() {
		$msg = $this->msg( $this->getHeaderMessageKey() );

		if ( $this->isBundled() ) {
			$count = $this->getNotificationCountForOutput();
			// Repeat is B/C until unused parameter is removed from translations
			$msg->numParams( $count, $count );
			$msg->params( $this->getTruncatedTitleText( $this->event->getTitle(), true ) );
		} else {
			$msg->params( $this->getAgentForOutput() );
			$msg->params( $this->getTruncatedTitleText( $this->event->getTitle(), true ) );
			$msg->plaintextParams( $this->getTopicTitle() );
		}

		return $msg;
	}

	/** @inheritDoc */
	public function getCompactHeaderMessage() {
		$msg = $this->msg( 'notification-compact-header-flow-new-topic' );
		$msg->plaintextParams( $this->getTopicTitle() );
		return $msg;
	}
}
