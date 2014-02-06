<?php

namespace Flow;

use Flow\Model\Workflow;
use Html;
use IContextSource;
use ContextSource;
use Xml;

class View extends ContextSource {
	function __construct(
		Templating $templating,
		UrlGenerator $urlGenerator,
		IContextSource $requestContext
	) {
		$this->templating = $templating;
		$this->urlGenerator = $urlGenerator;
		$this->setContext( $requestContext );
	}

	public function show( WorkflowLoader $loader, $action ) {
		$out = $this->getOutput();
		$out->addModuleStyles( array( 'mediawiki.ui', 'mediawiki.ui.button', 'ext.flow.base' ) );
		$out->addModules( array( 'ext.flow.base', 'ext.flow.editor' ) );

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

		// Re-implement the former SimpleAntiSpam extension
		$out->addHTML(
			Xml::openElement( 'div', array( 'id' => 'antispam-container', 'style' => 'display: none;' ) )
			. Html::rawElement( 'label', array( 'for' => 'wpAntiSpam' ), $this->msg( 'simpleantispam-label' )->parse() )
			. Xml::element( 'input', array( 'type' => 'text', 'name' => 'wpAntispam', 'id' => 'wpAntispam', 'value' => '' ) )
			. Xml::closeElement( 'div' )
		);

		if ( $request->wasPosted() ) {
			global $wgFlowTokenSalt;
			if ( $request->getVal( 'wpEditToken' ) != $user->getEditToken( $wgFlowTokenSalt ) ) {
				$this->error( $this->msg( 'sessionfailure' )->escaped() );
			} elseif ( $spam = $request->getText( 'wpAntispam' ) !== '' ) {
				wfDebugLog(
					'SimpleAntiSpam',
					$this->getUser()->getName() .
					' editing "' .
					$this->getTitle()->getPrefixedText() .
					'" submitted bogus field "' .
					$spam .
					'"'
				);
				$this->error( $this->msg( 'spamprotectionmatch' )->params( $spam )->escaped() );
			} else {
				$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
				if ( $blocksToCommit ) {
					$loader->commit( $workflow, $blocksToCommit );
					$this->redirect( $workflow, 'view' );
					return;
				}
			}
		}

		$workflowId = $workflow->isNew() ? '' : $workflow->getId()->getHex();
		$title = $workflow->getArticleTitle();
		$out->addHTML( Html::openElement( 'div',
			array(
				'class' => 'flow-container',
				'data-workflow-id' => $workflowId,
				'data-page-title' => $title->getPrefixedText(),
				'data-workflow-existence' => $workflow->isNew() ? 'new' : 'existing',
			)
		) );
		foreach ( $blocks as $block ) {
			$block->render( $this->templating, $request->getArray( $block->getName(), array() ) );
		}
		$out->addHTML( "</div>" );
	}

	/**
	 * @param string $msg HTML text to output
	 */
	protected function error( $msg ) {
		$this->getOutput()->addHTML( '<div class="error">' . $msg . '</div>' );
	}

	protected function redirect( Workflow $workflow, $action = 'view', array $query = array() ) {
		$url = $this->urlGenerator->generateUrl( $workflow, $action, $query );
		$this->getOutput()->redirect( $url );
	}
}
