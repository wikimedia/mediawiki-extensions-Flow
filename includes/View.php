<?php

namespace Flow;

use Flow\Exception\InvalidActionException;
use Flow\Model\Workflow;
use Html;
use IContextSource;
use ContextSource;

class View extends ContextSource {
	function __construct(
		Templating $templating,
		IContextSource $requestContext
	) {
		$this->templating = $templating;
		$this->setContext( $requestContext );
	}

	public function show( WorkflowLoader $loader, $action ) {
		$out = $this->getOutput();
		$out->addModuleStyles( array( 'mediawiki.ui', 'mediawiki.ui.button', 'ext.flow.base.styles' ) );
		$out->addModules( array( 'ext.flow.base', 'ext.flow.editor' ) );

		// Allow other extensions to add modules
		wfRunHooks( 'FlowAddModules', array( $out ) );

		$workflow = $loader->getWorkflow();

		$title = $workflow->getArticleTitle();
		$out->setPageTitle( $title->getPrefixedText() );
		// Temporary hack to make relative links work when the page is requested as /w/index.php?title=
		// @todo this wont work when we eventually display posts from multiple source pages,
		// @todo Patch core to either deprecate /w/index.php?title= and issue redirects, or
		//   include the <base href="..."> directly from core
		$out->prependHTML( Html::element( 'base', array(
			'href' => $title->getLocalURL()
		) ) );

		$request = $this->getRequest();
		$user = $this->getUser();

		$blocks = $loader->createBlocks();
		foreach ( $blocks as $block ) {
			$block->init( $action, $user );
		}

		if ( $request->wasPosted() ) {
			if ( $request->getVal( 'wpEditToken' ) != $user->getEditToken() ) {
				$error = '<div class="error">' . $this->msg( 'sessionfailure' ) . '</div>';
				$out->addHTML( $error );
			} else {
				$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
				if ( $blocksToCommit ) {
					$loader->commit( $workflow, $blocksToCommit );
					$this->redirect( $workflow );
					return;
				}
			}
		}

		$workflowId = $workflow->isNew() ? '' : $workflow->getId()->getAlphadecimal();
		$title = $workflow->getArticleTitle();
		$out->addHTML( Html::openElement( 'div',
			array(
				'class' => 'flow-container',
				'data-workflow-id' => $workflowId,
				'data-page-title' => $title->getPrefixedText(),
				'data-workflow-existence' => $workflow->isNew() ? 'new' : 'existing',
			)
		) );

		$parameters = $loader->extractBlockParameters( $request, $blocks );

		$rendered = false;
		foreach ( $blocks as $block ) {
			$rendered |= $block->onRender( $action, $this->templating, $parameters[$block->getName()] );
		}
		if ( !$rendered ) {
			throw new InvalidActionException( "Unrecognized get action: " . $action, 'invalid-action' );
		}
		$out->addHTML( "</div>" );
		// Update newtalk and watchlist notification status on view action of any workflow
		// since the normal page view that resets notification status is not accessiable
		// anymore due to Flow occupation
		if ( $action === 'view' ) {
			$user->clearNotification( $title );
		}
	}

	protected function redirect( Workflow $workflow ) {
		$link = $this->templating->getUrlGenerator()->workflowLink(
			$workflow->getArticleTitle(),
			$workflow->getId()
		);
		$this->getOutput()->redirect( $link->getFullURL() );
	}
}
