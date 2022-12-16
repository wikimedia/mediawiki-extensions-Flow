<?php

namespace Flow\Actions;

use Article;
use IContextSource;
use MediaWiki\MediaWikiServices;
use OutputPage;
use Title;

class ViewAction extends FlowAction {

	/**
	 * @param Article $article
	 * @param IContextSource $context
	 */
	public function __construct( Article $article, IContextSource $context ) {
		parent::__construct( $article, $context, 'view' );
	}

	public function doesWrites() {
		return false;
	}

	public function showForAction( $action, OutputPage $output = null ) {
		parent::showForAction( $action, $output );

		$title = $this->context->getTitle();
		$watchlistNotificationManager = MediaWikiServices::getInstance()->getWatchlistManager();
		$watchlistNotificationManager->clearTitleUserNotifications( $this->context->getUser(), $title );

		$output ??= $this->context->getOutput();
		$output->addCategoryLinks( $this->getCategories( $title ) );
	}

	protected function getCategories( Title $title ) {
		$id = $title->getArticleID();
		if ( !$id ) {
			return [];
		}

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			/* from */ 'categorylinks',
			/* select */ [ 'cl_to', 'cl_sortkey' ],
			/* conditions */ [ 'cl_from' => $id ],
			__METHOD__
		);

		$categories = [];
		foreach ( $res as $row ) {
			$categories[$row->cl_to] = $row->cl_sortkey;
		}

		return $categories;
	}
}
