<?php

namespace Flow\Content;

use Article;
use ContentHandler;
use Flow\Container;
use Revision;
use Title;

abstract class Content {
	static function onGetDefaultModel( Title $title, &$model ) {
		$occupationController = Container::get( 'occupation_controller' );

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$model = 'flow-board';

			return false;
		}

		return true;
	}

	static function onShowMissingArticle( Article $article ) {
		if ( $article->getPage()->getContentModel() !== 'flow-board' ) {
			return true;
		}

		$emptyContent = ContentHandler::getForModelID( 'flow-board' )->makeEmptyContent();

		$parserOutput = $emptyContent->getParserOutput( $article->getTitle() );
		$article->getContext()->getOutput()->addParserOutput( $parserOutput );

		return false;
	}

	static function onFetchContentObject( Article &$article, \Content &$contentObject ) {
		$occupationController = Container::get( 'occupation_controller' );
		$title = $article->getTitle();

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$loader = Container::get('factory.loader.workflow')
				->createWorkflowLoader( $title );

			$newRev = $occupationController->ensureFlowRevision( $article, $loader->getWorkflow() );

			if ( $newRev ) {
				$article->mRevision = $newRev;
				$article->mContentObject = $newRev->getContent();
				$contentObject = $newRev->getContent();
			}
		}

		return true;
	}
}