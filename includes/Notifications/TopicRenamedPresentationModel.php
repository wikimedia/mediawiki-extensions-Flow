<?php

namespace Flow;

use Title;

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
		$links = array( $this->getAgentLink() );
		if ( $this->isUserTalkPage() ) {
			$links[] = $this->getDiffLink();
		} else {
			$links[] = $this->getBoardByNewestLink();
		}
		return $links;
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

	protected function getDiffLink() {
		/** @var UrlGenerator $urlGenerator */
		$urlGenerator = Container::get( 'url_generator' );
		$anchor = $urlGenerator->diffPostLink(
			Title::newFromText( $this->event->getExtraParam( 'topic-workflow' )->getAlphadecimal(), NS_TOPIC ),
			$this->event->getExtraParam( 'topic-workflow' ),
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
