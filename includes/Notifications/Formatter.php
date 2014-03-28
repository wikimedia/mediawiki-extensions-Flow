<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use EchoBasicFormatter;
use Title;

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
			 * @var \Language $wgLang
			 */
			global $wgLang; // Message::language is protected :(

			if ( isset( $extra['content'] ) && $extra['content'] ) {
				$content = $extra['content'];
				$content = strip_tags( $content );
				$content = trim( $content );
				$content = $wgLang->truncate( $content, 200 );

				$message->params( $content );
			} else {
				$message->params( '' );
			}
		} elseif ( $param === 'post-permalink' ) {
			$postId = $extra['post-id'];
			if ( !$postId instanceof UUID ) {
				throw new FlowException( 'Expected UUID but received ' . get_class( $postId ) );
			}
			// Take user to the post if there is only one target post,
			// otherwise, take user to the topic view
			$urlGenerator = $this->getUrlGenerator();
			$title = $event->getTitle();
			$workflowId = $extra['topic-workflow'];
			if ( $this->bundleData['raw-data-count'] <= 1 ) {
				$anchor = $urlGenerator->postLink( $title, $workflowId, $postId );
			} else {
				$anchor = $urlGenerator->workflowLink( $title, $workflowId );
			}
			$message->params( $anchor->getFullUrl() );
		} elseif ( $param === 'topic-permalink' ) {
			$anchor = $this->getUrlGenerator()->workflowLink( $extra['topic-workflow'] );
			$message->params( $anchor->getFullUrl() );
		} elseif ( $param == 'flow-title' ) {
			$title = $event->getTitle();
			if ( $title ) {
				$formatted = $this->formatTitle( $title );
			} else {
				$formatted = $this->getMessage( 'echo-no-title' )->text();
			}
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
	 * @param \EchoEvent $event
	 * @param \User $user The user receiving the notification
	 * @param string $destination The destination type for the link
	 * @return array including target and query parameters
	 * @throws FlowException
	 */
	protected function getLinkParams( $event, $user, $destination ) {
		$anchor = null;
		$title  = $event->getTitle();

		// Unfortunately this is not a Flow code path, so we have to reach
		//  into global state.
		$urlGenerator = $this->getUrlGenerator();

		// Set up link parameters based on the destination (or pass to parent)
		switch ( $destination ) {
			case 'flow-post':
				$postId = $event->getExtraParam( 'post-id' );
				if ( !$postId instanceof UUID ) {
					throw new FlowException( 'Expected UUID but received ' . get_class( $post ) );
				}
				$workflowId = $event->getExtraParam( 'topic-workflow' );
				if ( $postId && $workflowId && $title ) {
					// Take user to the post if there is only one target post,
					// otherwise, take user to the topic view
					if ( $this->bundleData['raw-data-count'] <= 1 ) {
						$anchor = $urlGenerator->postLink( $title, $workflowId, $postId );
					} else {
						$anchor = $urlGenerator->workflowLink( $title, $workflowId );
					}
				}
				break;

			case 'flow-board':
				if ( $title ) {
					$anchor = $urlGenerator->boardLink( $title );
				}
				break;

			case 'flow-topic':
				$workflowId = $event->getExtraParam( 'topic-workflow' );
				if ( $title && $workflowId ) {
					$anchor = $urlGenerator->workflowLink( $title, $workflowId );
				}
				break;

			default:
				return parent::getLinkParams( $event, $user, $destination );
		}

		if ( $anchor ) {
			return array( $anchor->title, $anchor->query );
		} else {
			return array( null, array() );
		}
	}

	/**
	 * @return UrlGenerator
	 */
	protected function getUrlGenerator() {
		if ( ! $this->urlGenerator ) {
			$container = Container::getContainer();

			$this->urlGenerator = $container['url_generator'];
		}

		return $this->urlGenerator;
	}
}
