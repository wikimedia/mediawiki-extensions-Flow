<?php

namespace Flow\Content;

use Article;
use ContentHandler;
use Flow\Container;
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
}