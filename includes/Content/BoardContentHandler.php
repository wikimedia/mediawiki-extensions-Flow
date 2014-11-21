<?php

namespace Flow\Content;

use Flow\Container;
use Flow\FlowActions;
use Flow\Model\UUID;
use FormatJson;
use MWException;

class BoardContentHandler extends \ContentHandler {
	public function __construct( $modelId ) {
		if ( $modelId !== CONTENT_MODEL_FLOW_BOARD ) {
			throw new MWException( __CLASS__." initialised for invalid content model" );
		}

		parent::__construct( CONTENT_MODEL_FLOW_BOARD, array( CONTENT_FORMAT_JSON ) );
	}

	public function isSupportedFormat( $format ) {
		// Necessary for backwards-compatability where
		// the format "json" was used
		if ( $format === 'json' ) {
			$format = CONTENT_FORMAT_JSON;
		}

		return parent::isSupportedFormat( $format );
	}

	/**
	 * Serializes a Content object of the type supported by this ContentHandler.
	 *
	 * @since 1.21
	 *
	 * @param \Content $content The Content object to serialize
	 * @param string|null $format The desired serialization format
	 * @return string Serialized form of the content
	 * @throws MWException
	 */
	public function serializeContent( \Content $content, $format = null ) {
		if ( ! $content instanceof BoardContent ) {
			throw new MWException( "Expected a BoardContent object, got a " . get_class( $content ) );
		}

		$info = array();

		if ( $content->getWorkflowId() ) {
			$info['flow-workflow'] = $content->getWorkflowId()->getAlphaDecimal();
		}

		return FormatJson::encode( $info );
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
		$uuid = null;

		if ( ! $info ) {
			// For transition from wikitext-type pages
			// Make a plain content object and then when we get a chance
			// we can insert a proper object.
			return $this->makeEmptyContent();
		} elseif ( isset( $info['flow-workflow'] ) ) {
			$uuid = UUID::create( $info['flow-workflow'] );
		}

		return new BoardContent( CONTENT_MODEL_FLOW_BOARD, $uuid );
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
	 * Don't let people turn random pages into
	 * Flow ones until we want them to.
	 *
	 * @param \Title $title
	 * @return bool
	 */
	public function canBeUsedOn( \Title $title ) {
		/** @var \Flow\TalkpageManager $manager */
		$manager = Container::get( 'occupation_controller' );
		return $manager->isTalkpageOccupied( $title );
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
		$container = Container::getContainer();
		/** @var FlowActions $actions */
		$actions = $container['flow_actions'];
		$output = array();

		foreach( $actions->getActions() as $action ) {
			$actionData = $actions->getValue( $action );
			if ( is_array( $actionData ) && isset( $actionData['handler-class'] ) ) {
				$output[$action] = $actionData['handler-class'];
			}
		}

		// Flow has its own handlling for action=edit
		$output['edit'] = 'Flow\Actions\EditAction';

		return $output;
	}
}
