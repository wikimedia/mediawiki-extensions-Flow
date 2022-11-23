<?php

namespace Flow\Actions;

use Article;
use IContextSource;
use Title;

class EditAction extends FlowAction {

	/**
	 * @param Article $article
	 * @param IContextSource $context
	 */
	public function __construct( Article $article, IContextSource $context ) {
		parent::__construct( $article, $context, 'edit' );
	}

	/**
	 * Flow doesn't support edit action, redirect to the title instead
	 */
	public function show() {
		$title = $this->context->getTitle();

		// There should always be a title since Flow page
		// is detected by title or namespace, adding this
		// to prevent some werid cases
		if ( !$title ) {
			$title = Title::newMainPage();
		}

		$this->context->getOutput()->redirect( $title->getFullURL() );
	}

	public function doesWrites() {
		return false;
	}
}
