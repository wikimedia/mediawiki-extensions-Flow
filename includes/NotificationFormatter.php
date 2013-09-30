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

	/**
	 * Helper function for getLink()
	 *
	 * @param EchoEvent $event
	 * @param User $user The user receiving the notification
	 * @param String $destination The destination type for the link
	 * @return Array including target and query parameters
	 */
	protected function getLinkParams( $event, $user, $destination ) {
		$target = null;
		$query  = array();
		$title  = $event->getTitle();
		// Set up link parameters based on the destination (or pass to parent)
		switch ( $destination ) {
			case 'flow-post':
				$post  = $event->getExtraParam( 'post-id' );
				$flow  = $event->getExtraParam( 'topic-workflow' );
				if ( $post && $flow && $title ) {
					$target = SpecialPage::getTitleFor( 'Flow', $title );
					$query = array(
						'topic[postId]' => $post->getHex(),
						'workflow' => $flow->getHex(),
						'action' => 'view'
					);
				}
				break;
			case 'flow-board':
				if ( $title ) {
					$target = SpecialPage::getTitleFor( 'Flow', $title );
				}
				break;
			default:
				return parent::getLinkParams( $event, $user, $destination );
		}
		return array( $target, $query );
	}
}
