<?php

namespace Flow;

use Title;

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
			$links = array( $this->getAgentLink() );
			if ( $this->isUserTalkPage() ) {
				$links[] = $this->getDiffLink();
			} else {
				$links[] = $this->getBoardLink();
			}
			return $links;
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
				return parent::getHeaderMessageKey() . '-user-talk';
			} else {
				return parent::getHeaderMessageKey() . '-v2';
			}
		}
	}

	public function getHeaderMessage() {
		$msg = $this->msg( $this->getHeaderMessageKey() );
		$msg->plaintextParams( $this->getTopicTitle() );
		$msg->params( $this->getViewingUserForGender() );
		return $msg;
	}

	public function getBodyMessage() {
		if ( $this->isUserTalkPage() ) {
			$msg = $this->msg( "notification-body-{$this->type}-user-talk" );
		} else {
			$msg = $this->msg( "notification-body-{$this->type}-v2" );
		}

		$msg->params( $this->getContentSnippet() );
		return $msg;
	}

	protected function getDiffLink() {
		/** @var UrlGenerator $urlGenerator */
		$urlGenerator = Container::get( 'url_generator' );
		$anchor = $urlGenerator->diffPostLink(
			Title::newFromText( $this->event->getExtraParam( 'topic-workflow' )->getAlphadecimal(), NS_TOPIC ),
			$this->event->getExtraParam( 'post-id' ),
			$this->event->getExtraParam( 'revision-id' )
		);

		return array(
			'url' => $anchor->getFullURL(),
			'label' => $this->msg( 'notification-link-text-view-changes' )->params( $this->getViewingUserForGender() )->text(),
			'description' => '',
			'icon' => 'changes',
			'prioritized' => true,
		);
	}
}
