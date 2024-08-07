<?php

namespace Flow\Notifications;

use MediaWiki\Extension\Notifications\Formatters\EchoMentionStatusPresentationModel;
use MediaWiki\Title\Title;

class MentionStatusPresentationModel extends EchoMentionStatusPresentationModel {

	/** @inheritDoc */
	public function getPrimaryLink() {
		return array_merge(
			parent::getPrimaryLink(),
			[ 'url' => $this->getTopicOrPostUrl() ]
		);
	}

	/**
	 * @return string
	 */
	private function getTopicOrPostUrl() {
		$workflowId = $this->event->getExtraParam( 'topic-workflow' );
		$postId = $this->event->getExtraParam( 'post-id' );
		$fragment = '';
		$query = [ 'fromnotif' => 1 ];
		if ( $postId ) {
			$fragment = 'flow-post-' . $postId->getAlphadecimal();
			$query[ 'topic_showPostId' ] = $postId->getAlphadecimal();
		}
		$topicTitle = Title::makeTitleSafe(
			NS_TOPIC,
			$workflowId->getAlphadecimal(),
			$fragment
		);
		return $topicTitle->getFullURL( $query );
	}
}
