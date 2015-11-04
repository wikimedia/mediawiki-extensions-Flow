<?php

namespace Flow\Actions;

use IContextSource;
use Page;
use Title;

class EditAction extends FlowAction {

	function __construct( Page $page, IContextSource $context ) {
		parent::__construct( $page, $context, 'edit' );
	}

	/**
	 * Flow doesn't support edit action, redirect to the title instead
	 */
	public function show() {
		$title = $this->context->getTitle();

		// There should always be a title since Flow page
		// is detected by title or namespace, adding this
		// to prevent some weird cases
		if ( !$title ) {
			$title = Title::newMainPage();
		}

		// Forward values to preload in the form when adding a new section (T107637).
		// Flow uses different URL parameter names for this than vanilla MediaWiki.
		// This way the same URL works regardless of whether a page is a Flow or regular talk page.
		$request = $this->context->getRequest();
		$query = array();
		if ( $request->getVal( 'section' ) === 'new' ) {
			// null values will not be included in the query
			$query['topiclist_preloadtitle'] = $request->getVal( 'preloadtitle', null );
			$query['topiclist_preload'] = $request->getVal( 'preload', null );
		}

		$this->context->getOutput()->redirect( $title->getFullURL( $query ) );
	}

}
