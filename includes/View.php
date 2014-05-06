<?php

namespace Flow;

use Flow\Exception\InvalidActionException;
use Flow\Model\Workflow;
use Html;
use IContextSource;
use ContextSource;

class View extends ContextSource {
	/**
	 * @var Templating $templating
	 */
	protected $templating;

	/**
	 * @var UrlGenerator $urlGenerator
	 */
	protected $urlGenerator;

	/**
	 * @var TemplateHelper $lightncandy
	 */
	protected $lightncandy;

	function __construct(
		Templating $templating,
<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
		UrlGenerator $urlGenerator,
		IContextSource $requestContext,
		TemplateHelper $lightncandy
=======
		IContextSource $requestContext
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
	) {
		$this->templating = $templating;
		$this->setContext( $requestContext );
		$this->lightncandy = $lightncandy;
	}

	public function show( WorkflowLoader $loader, $action ) {
		global $wgFlowTokenSalt;

		wfProfileIn( __CLASS__ . '-init' );

		$out = $this->getOutput();
		$out->addModuleStyles( array( 'mediawiki.ui', 'mediawiki.ui.button', 'ext.flow.new.styles' ) );
		$out->addModules( array( 'ext.flow.new' ) );

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
		wfProfileOut( __CLASS__ . '-init' );

<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
		$wasPosted = $request->wasPosted();
		if ( $wasPosted ) {
			wfProfileIn( __CLASS__ . '-submit' );
			$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
			if ( $blocksToCommit ) {
				if ( $request->getVal( 'wpEditToken' ) != $user->getEditToken( $wgFlowTokenSalt ) ) {
					$blocks = $blocksToCommit;
					foreach ( $blocks as $block ) {
						$block->addError( 'edit-token', $this->msg( 'sessionfailure' ) );
					}
				} else {
=======
		if ( $request->wasPosted() ) {
			if ( $request->getVal( 'wpEditToken' ) != $user->getEditToken() ) {
				$error = '<div class="error">' . $this->msg( 'sessionfailure' ) . '</div>';
				$out->addHTML( $error );
			} else {
				$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
				if ( $blocksToCommit ) {
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
					$loader->commit( $workflow, $blocksToCommit );
<<<<<<< HEAD   (76e1f2 Merge "Revision single and diff view" into frontend-rewrite)
					$this->redirect( $workflow, 'view' );
					wfProfileOut( __CLASS__ . '-submit' );
=======
					$this->redirect( $workflow );
>>>>>>> BRANCH (73a9af Merge "Catch and specially handle InvalidArgumentException")
					return;
				}
			}
			wfProfileOut( __CLASS__ . '-submit' );
		}

		wfProfileIn( __CLASS__ . '-serialize' );
		// @todo This and API should use same code
		$apiResponse = array(
			'workflow' => $workflow->getId()->getAlphadecimal(),
			'blocks' => array(),
		);

		$parameters = $loader->extractBlockParameters( $request, $blocks );
		$editToken = $user->getEditToken( $wgFlowTokenSalt );
		foreach ( $blocks as $block ) {
			if ( $wasPosted ? $block->canSubmit( $action ) : $block->canRender( $action ) ) {
				$apiResponse['blocks'][] = $block->renderAPI( $this->templating, $parameters[$block->getName()] )
								+ array(
									'block-action-template' => $block->getTemplate( $action ),
									'editToken' => $editToken,
								);
			}
		}

		if ( count( $apiResponse['blocks'] ) === 0 ) {
			throw new InvalidActionException( "No blocks accepted action: $action" );
		}

		array_walk_recursive( $apiResponse, function( &$value ) {
			if ( $value instanceof \Message ) {
				$value = $value->text();
			}
		} );
		wfProfileOut( __CLASS__ . '-serialize' );

		/**
		header( 'Content-Type: application/json; content=utf-8' );
		$data = json_encode( $apiResponse );
		//return;
		die( $data );
		**/

		// Render with lightncandy. The exact template to render
		// will likely need to vary, but not yet.
		wfProfileIn( __CLASS__ . '-render' );
		$template = $this->lightncandy->getTemplate( 'flow_board' );
		$out->addHTML( $template( $apiResponse ) );
		wfProfileOut( __CLASS__ . '-render' );
	}

	protected function redirect( Workflow $workflow ) {
		$link = $this->templating->getUrlGenerator()->workflowLink(
			$workflow->getArticleTitle(),
			$workflow->getId()
		);
		$this->getOutput()->redirect( $link->getFullURL() );
	}
}
