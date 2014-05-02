<?php

namespace Flow\Content;

use Flow\Model\UUID;
use FormatJson;
use MWException;

class BoardContentHandler extends \ContentHandler {
	public function __construct( $modelId ) {
		if ( $modelId !== 'flow-board' ) {
			throw new MWException( __CLASS__." initialised for invalid content model" );
		}

		parent::__construct( 'flow-board', array( 'json' ) );
	}

	/**
	 * Serializes a Content object of the type supported by this ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @param Content $content The Content object to serialize
	 * @param string $format The desired serialization format
	 *
	 * @return string Serialized form of the content
	 */
	public function serializeContent( \Content $content, $format = null ) {
		if ( ! $content instanceof BoardContent ) {
			throw new MWException( "Expected a BoardContent object, got a " . get_class( $content ) );
		}

		return FormatJson::encode( array(
			'board-workflow' => $content->getWorkflowId()->getAlphaDecimal(),
		) );
	}

	/**
	 * Unserializes a Content object of the type supported by this ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @param string $blob Serialized form of the content
	 * @param string $format The format used for serialization
	 *
	 * @return Content The Content object created by deserializing $blob
	 */
	public function unserializeContent( $blob, $format = null ) {
		$info = FormatJson::decode( $blob, true );

		if ( ! $info ) {
			// For transition from wikitext-type pages
			// Make a plain content object and then when we get a chance
			// we can insert a proper object.
			return $this->makeEmptyContent();
		} elseif ( isset( $info['board-workflow'] ) ) {
			$uuid = UUID::create( $info['board-workflow'] );
		}

		return new BoardContent( 'flow-board', $uuid );
	}

	/**
	 * Creates an empty Content object of the type supported by this
	 * ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @return Content
	 */
	public function makeEmptyContent() {
		return new BoardContent;
	}

	/**
	 * Returns overrides for action handlers.
	 * Classes listed here will be used instead of the default one when
	 * (and only when) $wgActions[$action] === true. This allows subclasses
	 * to override the default action handlers.
	 *
	 * @since 1.21
	 *
	 * @return array Always an empty array.
	 */
	public function getActionOverrides() {
		$container = \Flow\Container::getContainer();
		$actions = $container['flow_actions'];
		$output = array();

		foreach( $actions->getActions() as $action ) {
			$actionData = $actions->getValue( $action );
			if ( is_array( $actionData ) ) {
				$output[$action] = $actionData['handler-class'];
			}
		}

		return $output;
	}
}