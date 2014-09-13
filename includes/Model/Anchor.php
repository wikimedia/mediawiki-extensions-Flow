<?php

namespace Flow\Model;

use Html;
use Message;
use RawMessage;
use Title;

/**
 * Represents a mutable anchor as a Message instance along with
 * a title, query parameters, and a fragment.
 */
class Anchor {
	/**
	 * @var Message
	 */
	public $message;

	/**
	 * @var Title
	 */
	public $title;

	/**
	 * @var array
	 */
	public $query = array();

	/**
	 * @var string
	 */
	public $fragment;

	/**
	 * @param Message|string $message Text content of the anchor
	 * @param Title $title Page the anchor points to
	 * @param array $query Query parameters for the anchor
	 * @param string|null $fragment URL fragment of the anchor
	 */
	public function __construct( $message, Title $title, array $query = array(), $fragment = null ) {
		$this->title = $title;
		$this->query = $query;
		$this->fragment = $fragment;
		$this->setMessage( $message );
	}

	/**
	 * @return string
	 */
	public function getLinkURL() {
		return $this->resolveTitle()->getLinkURL( $this->query );
	}
	/**
	 * @return string
	 */
	public function getFullURL() {
		return $this->resolveTitle()->getFullURL( $this->query );
	}

	/**
	 * @return string
	 */
	public function getCanonicalURL() {
		return $this->resolveTitle()->getCanonicalURL( $this->query );
	}

	/**
	 * @param string|null $content Optional
	 * @return string HTML
	 */
	public function toHtml( $content = null ) {
		$text = $this->message->text();

		// Should we instead use Linker?
		return Html::element(
			'a',
			array(
				'href' => $this->getLinkURL(),
				'title' => $text,
			),
			$content === null ? $text : $content
		);
	}

	public function toArray() {
		return array(
			'url' => $this->getLinkURL(),
			'title' => $this->message->text()
		);
	}

	/**
	 * @return Title
	 */
	public function resolveTitle() {
		$title = $this->title;
		if ( $this->fragment !== null ) {
			$title = clone $title;
			$title->setFragment( $this->fragment );
		}

		return $title;
	}

	/**
	 * @param Message|string $message Text content of the anchor
	 */
	public function setMessage( $message ) {
		if ( $message instanceof Message ) {
			$this->message = $message;
		} else {
			// wrap non-messages into a message class
			$this->message = new RawMessage(
				'$1',
				// rawParam + htmlspecialchars ensures this wont be treated as wikitext
				// if the message gets parsed and that its still safe for html output.
				// NOTE: This can result in double encoding, prefer to use explicit messages
				// instead of this fallback.
				array( Message::rawParam( htmlspecialchars( $message ) ) )
			);
		}
	}
}
