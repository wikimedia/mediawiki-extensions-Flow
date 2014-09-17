<?php

namespace Flow\Content;

use Flow\Exception\FlowException;
use Flow\WorkflowLoaderFactory;
use Article;
use ContentHandler;
use Flow\Container;
use Title;

abstract class Content {
	static function onShowMissingArticle( Article $article ) {
		if ( $article->getPage()->getContentModel() !== 'flow-board' ) {
			return true;
		}

		if ( $article->getTitle()->getNamespace() === NS_TOPIC ) {
			// @todo pretty message about invalid workflow
			throw new FlowException( 'Non-existent topic' );
		}

		$emptyContent = ContentHandler::getForModelID( 'flow-board' )->makeEmptyContent();

		$parserOutput = $emptyContent->getParserOutput( $article->getTitle() );
		$article->getContext()->getOutput()->addParserOutput( $parserOutput );

		return false;
	}
}
