<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use EchoBasicFormatter;
use EchoEvent;
use Title;
use User;

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
			if ( isset( $extra['content'] ) && $extra['content'] ) {
				// @todo assumes content is html, make explicit
				$message->params( Utils::htmlToPlaintext( $extra['content'], 200 ) );
			} else {
				$message->params( '' );
			}
		} elseif ( $param === 'post-permalink' ) {
			$anchor = $this->getPostLinkAnchor( $event, $user );
			if ( $anchor ) {
				$message->params( $anchor->getFullUrl() );
			} else {
				$message->params( '' );
			}
		} elseif ( $param === 'topic-permalink' ) {
			$anchor = $this->getUrlGenerator()->workflowLink( $event->getTitle(), $extra['topic-workflow'] );
			$anchor->query['fromnotif'] = 1;
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
	 * Helper method for generating a link to post notification
	 * @param \EchoEvent
	 * @param \User
	 * @return Anchor|boolean
	 */
	protected function getPostLinkAnchor( EchoEvent $event, User $user ) {
		$urlGenerator = $this->getUrlGenerator();
		$workflowId = $event->getExtraParam( 'topic-workflow' );
		if ( !$workflowId instanceof UUID ) {
			throw new FlowException( 'No topic-workflow available for event ' . $event->getId() );
		}

		// Get topic title
		$title  = Title::makeTitleSafe( NS_TOPIC, $workflowId->getAlphadecimal() );
		$anchor = false;
		if ( $workflowId && $title ) {
			// Take user to the post if there is only one target post,
			// otherwise, take user to the first unread post of topic
			if ( $this->bundleData['raw-data-count'] <= 1 ) {
				$postId = $event->getExtraParam( 'post-id' );
				if ( !$postId instanceof UUID ) {
					throw new FlowException( 'Expected UUID but received ' . get_class( $postId ) );
				}
				$anchor = $urlGenerator->postLink( $title, $workflowId, $postId );
			} else {
				$postId = $this->getFirstUnreadPostId( $event, $user );
				if ( $postId ) {
					$anchor = $urlGenerator->postLink( $title, $workflowId, $postId );
				} else {
					$anchor = $urlGenerator->topicLink( $title, $workflowId );
				}
			}
		}

		$anchor->query['fromnotif'] = 1;

		return $anchor;
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
				$anchor = $this->getPostLinkAnchor( $event, $user );
				break;

			case 'flow-board':
				if ( $title ) {
					$anchor = $urlGenerator->boardLink( $title );
				}
				break;

			case 'flow-topic':
				$workflowId = $event->getExtraParam( 'topic-workflow' );
				// Get topic title
				$title  = Title::makeTitleSafe( NS_TOPIC, $workflowId->getAlphadecimal() );
				if ( $title && $workflowId ) {
					$anchor = $urlGenerator->topicLink( $title, $workflowId );
				}
				break;

			default:
				return parent::getLinkParams( $event, $user, $destination );
		}

		if ( $anchor ) {
			$anchor->query['fromnotif'] = 1;
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

	/**
	 * Get the very first unread post from a topic in an event
	 * @param \EchoEvent
	 * @param \User
	 * @return UUID|boolean
	 */
	protected function getFirstUnreadPostId( $event, $user ) {
		$data = $this->getBundleLastRawData( $event, $user );
		if ( $data ) {
			// Remove the check once the corresponding Echo patch is
			// merged, $data should be always an instance of EchoEvent
			if ( $data instanceof \EchoEvent ) {
				$extra = $data->getExtra();
			} else {
				$extra = $data->event_extra;
			}
			if ( isset( $extra['post-id'] ) ) {
				return $extra['post-id'];
			}
		}

		return false;
	}

	/**
	 * We don't show the text snippet for Flow bundled notification
	 * @param \EchoEvent
	 * @param \User
	 */
	protected function formatCommentText( $event, $user ) {
		if ( $this->bundleData['raw-data-count'] > 1 ) {
			return '';
		} else {
			return parent::formatCommentText( $event, $user );
		}
	}
}
