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
			&& $this->hasValidPostId()
			// we don't want to display notifications for the very first post (which
			// is submitted along with the title) because users will already receive
			// a flow-new-topic for that
			&& !static::isFirstPost( $this->event->getExtraParam( 'post-id' ), $this->event->getExtraParam( 'topic-workflow' ) );
	}

	public function getPrimaryLink() {
		if ( $this->isBundled() ) {
			// "Strict standards: Only variables should be passed by reference" in older PHP versions
			$bundledEvents = $this->getBundledEvents();
			$event = end( $bundledEvents );
		} else {
			$event = $this->event;
		}
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

	protected function getHeaderMessageKey() {
		if ( $this->isBundled() ) {
			if ( $this->isUserTalkPage() ) {
				return "notification-bundle-header-{$this->type}-user-talk";
			} else {
				return "notification-bundle-header-{$this->type}";
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
			list( $formattedCount, $countForPlural ) = $this->getNotificationCountForOutput();
			$msg = $this->msg( $this->getHeaderMessageKey() );
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
			if ( $this->isUserTalkPage() ) {
				$msg = $this->msg("notification-body-{$this->type}-v2");
			} else {
				$msg = $this->msg("notification-body-{$this->type}-user-talk");
			}
			$msg->params( $this->getContentSnippet() );
			return $msg;
		}
	}

	/**
	 * @param UUID $postId
	 * @param UUID $workflowId
	 * @return bool
	 */
	public static function isFirstPost( UUID $postId, UUID $workflowId ) {
		/*
		 * We don't want to go fetch the entire topic tree, so we'll use a crude
		 * technique to figure out if we're dealing with the first post: check if
		 * they were posted at (almost) the exact same time.
		 * If they're more than 1 second apart, it's very likely a not-first-post
		 * (or a very slow server, upgrade your machine!). False positives on the
		 * other side are also very rare: who on earth can refresh the page, read
		 * the post and write a meaningful reply in just 1 second? :)
		 */
		$diff = $postId->getTimestamp( TS_UNIX ) - $workflowId->getTimestamp( TS_UNIX );
		return $diff <= 1;
	}
}
