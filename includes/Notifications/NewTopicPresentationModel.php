<?php

namespace Flow;

use EchoEventPresentationModel;

class NewTopicPresentationModel extends EchoEventPresentationModel {

	public function canRender() {
		return (bool)$this->event->getTitle();
	}

	public function getIconType()
	{
		return 'flow-discussion';
	}

	public function getPrimaryLink()
	{
		/** @var UrlGenerator $urlGenerator */
		$urlGenerator = Container::get( 'url_generator' );
		$url = $urlGenerator->boardLink( $this->event->getTitle(), 'newest' )->getFullURL();
		$msg = $this->msg( 'flow-notification-link-text-view-topic' );
		return array( $url, $msg );
	}

	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();
		$msg->params( $this->event->getTitle() );
		$msg->params( $this->event->getExtra()['topic-title'] );
		return $msg;
	}

	public function getBodyMessage() {
		$msg = $this->msg( 'notification-body-flow-new-topic' );
		list( $formattedName, $genderName ) = $this->getAgentForOutput();
		$msg->params( $formattedName, $genderName );
		$msg->params( $this->event->getTitle() );
		$msg->params( $this->event->getExtra()['topic-title'] );
		return $msg;
	}

}