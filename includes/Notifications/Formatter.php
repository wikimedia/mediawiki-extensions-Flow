<?php

use Flow\UrlGenerator;
use Flow\Container;

class FlowCommentFormatter extends EchoBasicFormatter {
	protected $urlGenerator;

	protected function processParam( $event, $param, $message, $user ) {
		$extra = $event->getExtra();
		if ( $param === 'flow-board' ) {
			$title = $event->getTitle();
			$boardTitle = Special::getTitleFor( 'Flow', $title->getPrefixedText() );
			$output = $this->formatTitle( $boardTitle );
			$message->params( $output );
		} elseif ( $param === 'subject' ) {
			if ( isset( $extra['topic-title'] ) && $extra['topic-title'] ) {
				$message->params( trim($extra['topic-title']) );
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
			$url = $this->getUrlGenerator()->buildUrl(
				$event->getTitle(),
				'view',
				array(
					'topic[postId]' => $this->getRelevantPostIdForNotification( $event, $user, $postId )->getHex(),
					'workflow' => $extra['topic-workflow']->getHex(),
				)
			);

			$message->params( $url );
		} elseif ( $param === 'topic-permalink' ) {
			$url = $this->getUrlGenerator()->buildUrl(
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
			$message->params( trim($extra['old-subject']) );
		} elseif ( $param == 'new-subject' ) {
			$message->params( trim($extra['new-subject']) );
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

		// Unfortunately this is not a Flow code path, so we have to reach
		//  into global state.
		$container = Flow\Container::getContainer();
		$urlGenerator = $container['url_generator'];

		// Set up link parameters based on the destination (or pass to parent)
		switch ( $destination ) {
			case 'flow-post':
				$post  = $event->getExtraParam( 'post-id' );
				$flow  = $event->getExtraParam( 'topic-workflow' );
				if ( $post && $flow && $title ) {
					list( $target, $query ) =
						$urlGenerator->generateUrlData( $flow, array(
							'topic[postId]' => $this->getRelevantPostIdForNotification( $event, $user, $post )->getHex(),
						) );
				}
				break;
			case 'flow-board':
				if ( $title ) {
					list( $target, $query ) = $urlGenerator->buildUrlData( $title );
				}
				break;
			case 'flow-topic':
				$topic = $event->getExtraParam( 'topic-workflow' );

				list( $target, $query ) =
					$urlGenerator->generateUrlData( $topic );
				break;
			default:
				return parent::getLinkParams( $event, $user, $destination );
		}

		return array( $target, $query );
	}

	protected function getUrlGenerator() {
		if ( ! $this->urlGenerator ) {
			$container = Flow\Container::getContainer();

			$this->urlGenerator = $container['url_generator'];
		}

		return $this->urlGenerator;
	}

	/**
	 * Get the relevant post for notification, use topic id instead individual
	 * post id for bundle message on post-reply, post-edit, post-moderatation
	 */
	protected function getRelevantPostIdForNotification( $event, $user, $postId ) {
		$notifTypes = array( 'flow-post-reply', 'flow-post-edited', 'flow-post-moderated' );
		$container = Container::getContainer();

		// Use the topic id for notification if this is a bundle message
		if ( in_array( $event->getType(), $notifTypes ) && $this->bundleData['use-bundle'] ) {
			$treeRepository = $container['repository.tree'];
			$postId = $treeRepository->findRoot( $postId );
		}

		return $postId;
	}
}
