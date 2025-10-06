<?php

namespace Flow\Actions;

use MediaWiki\Context\IContextSource;
use MediaWiki\Deferred\LinksUpdate\CategoryLinksTable;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Article;
use MediaWiki\Title\Title;

class ViewAction extends FlowAction {

	public function __construct( Article $article, IContextSource $context ) {
		parent::__construct( $article, $context, 'view' );
	}

	public function doesWrites() {
		return false;
	}

	public function showForAction( $action, ?OutputPage $output = null ) {
		parent::showForAction( $action, $output );

		$title = $this->context->getTitle();
		$watchlistManager = MediaWikiServices::getInstance()->getWatchlistManager();
		$watchlistManager->clearTitleUserNotifications( $this->context->getUser(), $title );

		$output ??= $this->context->getOutput();
		$output->addCategoryLinks( $this->getCategories( $title ) );
	}

	protected function getCategories( Title $title ) {
		$id = $title->getArticleID();
		if ( !$id ) {
			return [];
		}

		$dbr = MediaWikiServices::getInstance()
			->getConnectionProvider()
			->getReplicaDatabase( CategoryLinksTable::VIRTUAL_DOMAIN );

		$res = $dbr->newSelectQueryBuilder()
			->select( [ 'lt_title', 'cl_sortkey' ] )
			->from( 'categorylinks' )
			->join( 'linktarget', null, 'lt_id = cl_target_id' )
			->where( [ 'cl_from' => $id ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$categories = [];
		foreach ( $res as $row ) {
			$categories[$row->lt_title] = $row->cl_sortkey;
		}

		return $categories;
	}
}
