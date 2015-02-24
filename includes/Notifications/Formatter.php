<?php

namespace Flow;

use Flow\Exception\FlowException;
use Flow\Model\Anchor;
use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Flow\Model\Workflow;
use EchoBasicFormatter;
use EchoEvent;
use Message;
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
			// link to individual new-topic

			if ( isset( $extra['topic-workflow'] ) ) {
				$title = Workflow::getFromTitleCache(
					wfWikiId(),
					NS_TOPIC,
					$extra['topic-workflow']->getAlphadecimal()
				);
			} else {
				$title = $event->getTitle();
			}

			$anchor = $this->getUrlGenerator()->workflowLink( $title, $extra['topic-workflow'] );
			$anchor->query['fromnotif'] = 1;
			$message->params( $anchor->getFullUrl() );
		} elseif ( $param === 'new-topics-permalink' ) {
			// link to board sorted by newest topics
			$anchor = $this->getUrlGenerator()->boardLink( $event->getTitle(), 'newest' );
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
	 * @param EchoEvent $event
	 * @param User $user
	 * @return Anchor|boolean
	 * @throws FlowException
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

		// Unfortunately this is not a Flow code path, so we have to reach
		//  into global state.
		$urlGenerator = $this->getUrlGenerator();

		// Set up link parameters based on the destination (or pass to parent)
		switch ( $destination ) {
			case 'flow-post':
				$anchor = $this->getPostLinkAnchor( $event, $user );
				break;

			case 'flow-topic':
				$workflowId = $event->getExtraParam( 'topic-workflow' );
				if ( !$workflowId instanceof UUID ) {
					break;
				}
				// Get topic title
				$title  = Title::makeTitleSafe( NS_TOPIC, $workflowId->getAlphadecimal() );
				if ( $title ) {
					$anchor = $urlGenerator->topicLink( $title, $workflowId );
				}
				break;

			case 'flow-new-topics':
				$title  = $event->getTitle();
				if ( $title ) {
					$anchor = $urlGenerator->boardLink( $title, 'newest' );
				}
				break;

			default:
				return parent::getLinkParams( $event, $user, $destination );
		}

		if ( $anchor ) {
			$anchor->query['fromnotif'] = 1;
			return array( $anchor->resolveTitle(), $anchor->query );
		} else {
			return array( null, array() );
		}
	}

	/**
	 * @return UrlGenerator
	 */
	protected function getUrlGenerator() {
		if ( ! $this->urlGenerator ) {
			$this->urlGenerator = Container::get( 'url_generator' );
		}

		return $this->urlGenerator;
	}

	/**
	 * Get the very first unread post from a topic in an event
	 * @param \EchoEvent
	 * @param \User
	 * @return UUID|false
	 */
	protected function getFirstUnreadPostId( $event, $user ) {
		$data = $this->getBundleLastRawData( $event, $user );
		if ( $data ) {
			// Remove the check once the corresponding Echo patch is
			// merged, $data should be always an instance of EchoEvent
			if ( $data instanceof \EchoEvent ) {
				$extra = $data->getExtra();
			} elseif ( isset( $data->event_extra ) ) {
				$extra = $data->event_extra;
			}
			if ( isset( $extra['post-id'] ) ) {
				return $extra['post-id'];
			}
		}

		return false;
	}
}

/**
 * @FIXME - Move bundle iterator logic into a centralized place in Echo and
 * introduce bundle type param like 'agent', 'page', 'event' so child formatter
 * only needs to specify what iterator to use
 */
class NewTopicFormatter extends NotificationFormatter {

	/**
	 * New Topic user 'event' as the iterator
	 */
	protected function generateBundleData( $event, $user, $type ) {
		$data = $this->getRawBundleData( $event, $user, $type );

		if ( !$data ) {
			return;
		}

		// bundle event is excluding base event
		$this->bundleData['event-count'] = count( $data ) + 1;
		$this->bundleData['use-bundle']  = $this->bundleData['event-count'] > 1;
	}

	/**
	 * @param $event EchoEvent
	 * @param $param string
	 * @param $message Message
	 * @param $user User
	 */
	protected function processParam( $event, $param, $message, $user ) {
		switch ( $param ) {
			case 'event-count':
				$message->numParams( $this->bundleData['event-count'] );
				break;
			default:
				parent::processParam( $event, $param, $message, $user );
				break;
		}
	}
}
