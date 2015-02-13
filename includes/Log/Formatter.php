<?php

namespace Flow\Log;

use Flow\Model\UUID;
use Flow\Parsoid\Utils;
use Message;

class Formatter extends \LogFormatter {
	/**
	 * Formats an activity log entry.
	 *
	 * @return string The log entry
	 */
	protected function getActionMessage() {
		$type = $this->entry->getType();
		$action = $this->entry->getSubtype();
		$title = $this->entry->getTarget();
		$skin = $this->plaintext ? null : $this->context->getSkin();
		$params = $this->entry->getParameters();

		// @todo: we should probably check if user isAllowed( <this-revision>, 'log' )
		// unlike RC, Contributions, ... this one does not batch-load all Flow
		// revisions & does not use the same Formatter, i18n message text, etc
		// I assume this will change with https://trello.com/c/S10KfqBm/62-8-history-watchlist-rc-and-contribs-changes
		// Then, we should also add in the isAllowed check!

		// FIXME this is ugly. Why were we treating log parameters as
		// URL GET parameters in the first place?
		if ( isset( $params['postId'] ) ) {
			$title = clone $title;
			$postId = $params['postId'];
			if ( $postId instanceof UUID ) {
				$postId = $postId->getAlphadecimal();
			}
			$title->setFragment( '#flow-post-' . $postId );
			unset( $params['postId'] );
		}

		if ( isset( $params['topicId'] ) ) {
			$title = clone $title;
			$topicId = $params['topicId'];
			if ( $topicId instanceof UUID ) {
				$topicId = $topicId->getAlphadecimal();
			}

			$title->setFragment( '#flow-topic-' . $topicId );
			unset( $params['topicId'] );
		}

		// Give grep a chance to find the usages:
		// logentry-delete-flow-delete-post, logentry-delete-flow-restore-post,
		// logentry-suppress-flow-restore-post, logentry-suppress-flow-suppress-post,
		// logentry-delete-flow-delete-topic, logentry-delete-flow-restore-topic,
		// logentry-suppress-flow-restore-topic, logentry-suppress-flow-suppress-topic,
		$message = $this->msg( "logentry-$type-$action" )
			->params( array(
				Message::rawParam( $this->getPerformerElement() ),
				$this->entry->getPerformer()->getId(),
				$title,
				$title->getFullUrl( $params ),
			) )
			->parse();

		return \Html::rawElement(
			'span',
			array( 'class' => 'plainlinks' ),
			$message
		);
	}

	/**
	 * The native LogFormatter::getActionText provides no clean way of handling
	 * the Flow action text in a plain text format (e.g. as used by CheckUser)
	 *
	 * @return string
	 */
	public function getActionText() {
		$text = $this->getActionMessage();
		return $this->plaintext ? Utils::htmlToPlaintext( $text ) : $text;
	}
}
