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
		UrlGenerator $urlGenerator,
		IContextSource $requestContext,
		TemplateHelper $lightncandy
	) {
		$this->templating = $templating;
		$this->urlGenerator = $urlGenerator;
		$this->setContext( $requestContext );
		$this->lightncandy = $lightncandy;
	}

	public function show( WorkflowLoader $loader, $action ) {
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

		if ( $request->wasPosted() ) {
			wfProfileIn( __CLASS__ . '-submit' );
			global $wgFlowTokenSalt;
			if ( $request->getVal( 'wpEditToken' ) != $user->getEditToken( $wgFlowTokenSalt ) ) {
				$error = '<div class="error">' . $this->msg( 'sessionfailure' ) . '</div>';
				$out->addHTML( $error );
			} else {
				$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
				if ( $blocksToCommit ) {
					$loader->commit( $workflow, $blocksToCommit );
					$this->redirect( $workflow, 'view' );
					wfProfileOut( __CLASS__ . '-submit' );
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
		foreach ( $blocks as $block ) {
			if ( $block->canRender( $action ) ) {
				$apiResponse['blocks'][] = array(
					'block-action-template' => $block->getTemplate( $action )
				) + $block->renderAPI( $this->templating, $parameters[$block->getName()] );
			}
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

	protected function redirect( Workflow $workflow, $action = 'view', array $query = array() ) {
		$url = $this->urlGenerator->generateUrl( $workflow, $action, $query );
		$this->getOutput()->redirect( $url );
	}
}
