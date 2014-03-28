<?php

namespace Flow;

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
		if ( $message instanceof Message ) {
			$this->message = $message;
		} else {
			// wrap non-messages into a message class
			$this->message = new RawMessage( '$1', array( $message ) );
		}
	}

	/**
	 * @return string
	 */
	public function getLocalURL() {
		return $this->resolveTitle()->getLocalURL( $this->query );
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
				'href' => $this->getFullUrl(),
				'title' => $text,
			),
			$content === null ? $text : $content
		);
	}

	/**
	 * @return Title
	 */
	protected function resolveTitle() {
		$title = $this->title;
		if ( $this->fragment !== null ) {
			$title = clone $title;
			$title->setFragment( $this->fragment );
		}

		return $title;
	}
}
