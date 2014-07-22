<?php

namespace Flow;

use Flow\Exception\InvalidActionException;
use Flow\Model\Workflow;
use Html;
use IContextSource;
use Linker;
use Message;
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
		TemplateHelper $lightncandy,
		IContextSource $requestContext
	) {
		$this->templating = $templating;
		$this->urlGenerator = $urlGenerator;
		$this->lightncandy = $lightncandy;
		$this->setContext( $requestContext );
	}

	public function show( WorkflowLoader $loader, $action ) {
		wfProfileIn( __CLASS__ . '-init' );

		$out = $this->getOutput();
		$styles = array(
			'mediawiki.ui',
			'mediawiki.ui.button',
			'ext.flow.styles',
			'ext.flow.mediawiki.ui.tooltips',
			'ext.flow.mediawiki.ui.form',
			'ext.flow.icons.styles',
			'ext.flow.board.styles',
			'ext.flow.board.topic.styles'
		);
		$out->addModuleStyles( $styles );
		$out->addModules( array( 'ext.flow.new' ) );

		// Allow other extensions to add modules
		wfRunHooks( 'FlowAddModules', array( $out ) );

		$workflow = $loader->getWorkflow();

		$title = $workflow->getArticleTitle();
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

		$wasPosted = $request->wasPosted();
		if ( $wasPosted ) {
			wfProfileIn( __CLASS__ . '-submit' );
			$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
			if ( $blocksToCommit ) {
				if ( !$user->matchEditToken( $request->getVal( 'wpEditToken' ) ) ) {
					// only render the failed blocks
					$blocks = $blocksToCommit;
					foreach ( $blocks as $block ) {
						$block->addError( 'edit-token', $this->msg( 'sessionfailure' ) );
					}
				} else {
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
			'workflow' => $workflow->isNew() ? '' : $workflow->getId()->getAlphadecimal(),
			'blocks' => array(),
		);

		$parameters = $loader->extractBlockParameters( $request, $blocks );
		$editToken = $user->getEditToken();
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
			if ( $value instanceof Anchor ) {
				$value = $value->toArray();
			} elseif ( $value instanceof Message ) {
				$value = $value->text();
			}
		} );
		wfProfileOut( __CLASS__ . '-serialize' );

		$this->setPageTitle( $workflow, $apiResponse );

		// Update newtalk and watchlist notification status on view action of any workflow
		// since the normal page view that resets notification status is not accessiable
		// anymore due to Flow occupation
		if ( $action === 'view' ) {
			$user->clearNotification( $title );
		}

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

	/**
	 * Set the title for the page, either the Flow board or the topic.
	 *
	 * @todo Provide more informative page title for actions other than view,
	 *       e.g. "Hide post in <TITLE>", "Reopen <TITLE>", etc.
	 */
	protected function setPageTitle( Workflow $workflow, array $apiResponse ) {
		$out = $this->getOutput();
		if ( $workflow->getType() === 'topic' ) {
			$title = $workflow->getOwnerTitle();
			$out->setPageTitle( $this->msg( 'flow-topic-first-heading', $title->getPrefixedText() ) );
			$out->setHtmlTitle( $apiResponse['blocks'][0]['topicTitle'] );
			$out->setSubtitle( '&lt; ' . Linker::link( $title ) );
		} else {
			$out->setPageTitle( $workflow->getArticleTitle()->getFullText() );
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
