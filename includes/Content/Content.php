<?php

namespace Flow\Content;

use Flow\Exception\FlowException;
use Flow\WorkflowLoaderFactory;
use Article;
use ContentHandler;
use Flow\Container;
use Title;

abstract class Content {
	static function onGetDefaultModel( Title $title, &$model ) {
		$occupationController = \FlowHooks::getOccupationController();

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			$model = CONTENT_MODEL_FLOW_BOARD;

			return false;
		}

		return true;
	}

	static function onShowMissingArticle( Article $article ) {
		if ( $article->getPage()->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			return true;
		}

		if ( $article->getTitle()->getNamespace() === NS_TOPIC ) {
			// @todo pretty message about invalid workflow
			throw new FlowException( 'Non-existent topic' );
		}

		$emptyContent = ContentHandler::getForModelID( CONTENT_MODEL_FLOW_BOARD )->makeEmptyContent();

		$parserOutput = $emptyContent->getParserOutput( $article->getTitle() );
		$article->getContext()->getOutput()->addParserOutput( $parserOutput );

		return false;
	}

	static function onFetchContentObject( Article &$article, \Content &$contentObject = null ) {
		if ( $contentObject === null ) {
			return true;
		}

		$occupationController = \FlowHooks::getOccupationController();
		$title = $article->getTitle();

		if ( $occupationController->isTalkpageOccupied( $title ) ) {
			/** @var WorkflowLoaderFactory $factory */
			$factory = Container::get( 'factory.loader.workflow' );
			$loader = $factory->createWorkflowLoader( $title );

			$newRev = $occupationController->ensureFlowRevision( $article, $loader->getWorkflow() );

			if ( $newRev ) {
				/** @noinspection PhpUndefinedFieldInspection */
				$article->getPage()->mRevision = $newRev;
				/** @noinspection PhpUndefinedFieldInspection */
				$article->getPage()->mContentObject = $newRev->getContent();
				$contentObject = $newRev->getContent();
			}
		}

		return true;
	}
}
