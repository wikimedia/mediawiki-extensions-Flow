<?php

use Flow\UrlGenerator;

class FlowCommentFormatter extends EchoBasicFormatter {
	protected function processParam( $event, $param, $message, $user ) {
		$extra = $event->getExtra();
		if ( $param === 'flow-board' ) {
			$title = $event->getTitle();
			$boardTitle = Special::getTitleFor( 'Flow', $title->getPrefixedText() );
			$output = $this->formatTitle( $boardTitle );
			$message->params( $output );
		} elseif ( $param === 'subject' ) {
			if ( isset( $extra['topic-title'] ) && $extra['topic-title'] ) {
				$message->params( $extra['topic-title'] );
			} else {
				$message->params( '' );
			}
		} elseif ( $param === 'commentText' ) {
			/**
			 * @var $wgLang Language
			 */
			global $wgLang; // Message::language is protected :(

			if ( isset( $extra['content'] ) && $extra['content'] ) {
				$content = $extra['content'];
				$content = trim( $content );
				$content = $wgLang->truncate( $content, 200 );

				$message->params( $content );
			} else {
				$message->params( '' );
			}
		} elseif ( $param === 'post-permalink' ) {
			$postId = $extra['post-id'];
			$url = UrlGenerator::buildUrl(
				$event->getTitle(),
				'view',
				array(
					'topic[postId]' => $postId->getHex(),
					'workflow' => $extra['topic-workflow']->getHex(),
				)
			);

			$message->params( $url );
		} elseif ( $param === 'topic-permalink' ) {
			$url = UrlGenerator::buildUrl(
				$event->getTitle(),
				'view',
				array(
					'workflow' => $extra['topic-workflow']->getHex(),
				)
			);

			$message->params( $url );
		} elseif ( $param == 'flow-title' ) {
			$title = $this->formatTitle( SpecialPage::getTitleFor( 'Flow', $event->getTitle() ) );
			$message->params( $title );
		} elseif ( $param == 'old-subject' ) {
			$message->params( $extra['old-subject'] );
		} elseif ( $param == 'new-subject' ) {
			$message->params( $extra['new-subject'] );
		} else {
			parent::processParam( $event, $param, $message, $user );
		}
	}
}
