<?php

namespace Flow\OOUI;

class BoardDescriptionWidget extends \OOUI\Widget {

	protected $editButton;

	protected $description = '';

	public function __construct( array $config = [] ) {
		// Parent constructor
		parent::__construct( $config );

		if ( isset( $config['description'] ) ) {
			$this->description = $config['description'];
		}
		$editLink = null;
		if ( isset( $config['editLink'] ) ) {
			$editLink = $config['editLink'];
		}

		// Edit button
		$this->editButton = new \OOUI\ButtonWidget( [
			'framed' => false,
			'href' => $editLink,
			'label' => wfMessage( 'flow-edit-header-link' )->text(),
			'icon' => 'edit',
			'flags' => 'progressive',
			'classes' => [ 'flow-ui-boardDescriptionWidget-editButton', 'mw-parser-output' ]
		] );

		// Content
		$this->contentWrapper = $this->wrapInDiv(
			$this->description,
			[ 'flow-ui-boardDescriptionWidget-content' ]
		);

		// Initialize
		$this->addClasses( [ 'flow-ui-boardDescriptionWidget', 'flow-ui-boardDescriptionWidget-nojs' ] );

		if ( $editLink ) {
			$this->appendContent( $this->editButton );
		}
		$this->appendContent( $this->contentWrapper );
	}

	/**
	 * Wrap some content in a div
	 *
	 * @param string $content Content to wrap
	 * @param string $classes Classes to add to the div
	 * @return OOUI\Tag New div with content
	 */
	private function wrapInDiv( $content, $classes ) {
		$tag = new \OOUI\Tag( 'div' );
		$tag->addClasses( $classes );
		$tag->appendContent( new \OOUI\HtmlSnippet( $content ) );

		return $tag;
	}
}
