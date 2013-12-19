<?php

namespace Flow;

use Flow\Container;
use Flow\UrlGenerator;
use EchoBasicFormatter;

// could be renamed later if we have more formatters
class NotificationFormatter extends EchoBasicFormatter {
	protected $urlGenerator;

	protected function processParam( $event, $param, $message, $user ) {
		$extra = $event->getExtra();
		if ( $param === 'subject' ) {
			if ( isset( $extra['topic-title'] ) && $extra['topic-title'] ) {
				$this->processParamEscaped( $message, trim( $extra['topic-title'] ) );
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
			list( $title, $query ) = $this->getUrlGenerator()->generateUrlData(
				$extra['topic-workflow'],
				'view'
			);
			// Take user to the post if there is only one target post,
			// otherwise, take user to the topic view
			if ( $this->bundleData['raw-data-count'] <= 1 ) {
				$title->setFragment( '#flow-post-' . $postId->getPretty() );
			}
			$message->params( $title->getFullUrl( $query ) );
		} elseif ( $param === 'topic-permalink' ) {
			$url = $this->getUrlGenerator()->generateUrl( $extra['topic-workflow'] );

			$message->params( $url );
		} elseif ( $param == 'flow-title' ) {
			list( $title ) = $this->getUrlGenerator()->buildUrlData( $event->getTitle() );
			$formatted = $this->formatTitle( $title );
			$message->params( $formatted );
		} elseif ( $param == 'old-subject' ) {
			$this->processParamEscaped( $message, trim( $extra['old-subject'] ) );
		} elseif ( $param == 'new-subject' ) {
			$this->processParamEscaped( $message, trim( $extra['new-subject'] ) );
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
		$container = Container::getContainer();
		$urlGenerator = $container['url_generator'];

		// Set up link parameters based on the destination (or pass to parent)
		switch ( $destination ) {
			case 'flow-post':
				$post  = $event->getExtraParam( 'post-id' );
				$flow  = $event->getExtraParam( 'topic-workflow' );
				if ( $post && $flow && $title ) {
					list( $target, $query ) = $urlGenerator->generateUrlData( $flow );
					// Take user to the post if there is only one target post,
					// otherwise, take user to the topic view
					if ( $this->bundleData['raw-data-count'] <= 1 ) {
						$target->setFragment( '#flow-post-' . $post->getPretty() );
					}
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
			$container = Container::getContainer();

			$this->urlGenerator = $container['url_generator'];
		}

		return $this->urlGenerator;
	}
}
