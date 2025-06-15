<?php

namespace Flow\Actions;

use MediaWiki\Context\IContextSource;
use MediaWiki\MainConfigNames;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Article;
use MediaWiki\Title\Title;

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

		$services = MediaWikiServices::getInstance();
		$dbr = $services->getConnectionProvider()->getReplicaDatabase();

		$migrationStage = $services->getMainConfig()->get(
			MainConfigNames::CategoryLinksSchemaMigrationStage
		);

		$qb = $dbr->newSelectQueryBuilder()
			->from( 'categorylinks' );

		if ( $migrationStage & SCHEMA_COMPAT_READ_OLD ) {
			$qb->select( [ 'cl_to', 'cl_sortkey' ] );
		} else {
			$qb->select( [ 'cl_to' => 'lt_title', 'cl_sortkey' ] );
			$qb->join( 'linktarget', null, 'lt_id = cl_target_id' );
		}

		$res = $qb->where( [ 'cl_from' => $id ] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$categories = [];
		foreach ( $res as $row ) {
			$categories[$row->cl_to] = $row->cl_sortkey;
		}

		return $categories;
	}
}
