<?php

namespace Flow;

use Flow\Model\UUID;

class PostReplyPresentationModel extends FlowPresentationModel {

	public function getIconType() {
		return $this->isUserTalkPage() ? 'edit-user-talk' : 'chat';
	}

	public function canRender() {
		return $this->hasTitle()
			&& $this->hasValidTopicWorkflowId()
			&& $this->hasValidPostId();
	}

	public function getPrimaryLink() {
		$topmostPostID = null;

		if ( $this->isBundled() ) {
			// "Strict standards: Only variables should be passed by reference" in older PHP versions
			$bundledEvents = $this->getBundledEvents();

			$notificationController = Container::get( 'controller.notification' );
			$firstChronologicallyEvent = end( $bundledEvents );
			$firstChronologicallyPostId = $firstChronologicallyEvent->getExtraParam( 'post-id' );
			$bundledEventsIncludingThis = array_merge( array( $this->event ), $bundledEvents );
			$topmostPostID = $notificationController->getTopmostPostId( $bundledEventsIncludingThis ) ?:
				$firstChronologicallyPostId;

		} else {
			$event = $this->event;
			$firstChronologicallyPostId = $event->getExtraParam( 'post-id' );
		}
		return array(
			'url' => $this->getPostLinkUrl( $firstChronologicallyPostId, $topmostPostID ),
			'label' => $this->msg( 'flow-notification-link-text-view-post' )->text(),
		);
	}

	public function getSecondaryLinks() {
		if ( $this->isBundled() ) {
			return array( $this->getBoardLink() );
		} else {
			return array( $this->getAgentLink(), $this->getBoardLink() );
		}
	}

	protected function getHeaderMessageKey() {
		if ( $this->isBundled() ) {
			if ( $this->isUserTalkPage() ) {
				return "notification-bundle-header-{$this->type}-user-talk";
			} else {
				return "notification-bundle-header-{$this->type}-v2";
			}
		} else {
			if ( $this->isUserTalkPage() ) {
				return parent::getHeaderMessageKey() . "-user-talk";
			} else {
				return parent::getHeaderMessageKey();
			}
		}
	}

	public function getHeaderMessage() {
		if ( $this->isBundled() ) {
			$count = $this->getNotificationCountForOutput();
			$msg = $this->msg( $this->getHeaderMessageKey() );

			// Repeat is B/C until unused parameter is removed from translations
			$msg->numParams( $count, $count );
			$msg->plaintextParams( $this->getTopicTitle() );
			return $msg;
		} else {
			$msg = parent::getHeaderMessage();
			$msg->params( $this->getTruncatedTitleText( $this->event->getTitle(), true) );
			$msg->plaintextParams( $this->getTopicTitle() );
			return $msg;
		}
	}

	public function getBodyMessage() {
		if ( !$this->isBundled() ) {
			if ( $this->isUserTalkPage() ) {
				$msg = $this->msg("notification-body-{$this->type}-v2");
			} else {
				$msg = $this->msg("notification-body-{$this->type}-user-talk");
			}
			$msg->params( $this->getContentSnippet() );
			return $msg;
		}
	}

}
