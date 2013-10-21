<?php

namespace Flow;

use Flow\Model\Workflow;
use Html;
use IContextSource;

class View {
	function __construct(
		Templating $templating,
		UrlGenerator $urlGenerator,
		IContextSource $requestContext
	) {
		$this->templating = $templating;
		$this->urlGenerator = $urlGenerator;
		$this->context = $requestContext;
		$this->output = $this->context->getOutput();
	}

	public function show( WorkflowLoader $loader, $action ) {
		$this->output->addModuleStyles( array( 'mediawiki.ui' ) );
		$this->output->addModules( array( 'ext.flow.base', 'ext.flow.editor' ) );

		$workflow = $loader->getWorkflow();

		$title = $workflow->getArticleTitle();
		$this->output->setPageTitle( wfMessage( 'flow-specialpage', $title->getPrefixedText() )->text() );

		$request = $this->context->getRequest();
		$user = $this->context->getUser();

		$blocks = $loader->createBlocks();
		foreach ( $blocks as $block ) {
			$block->init( $action, $user );
		}

		if ( $request->getMethod() === 'POST' ) {
			global $wgFlowTokenSalt;
			if ( $request->getVal( 'wpEditToken' ) != $user->getEditToken( $wgFlowTokenSalt ) ) {
				$error = '<div class="error">' . wfMessage( 'sessionfailure' ) . '</div>';
				$this->output->addHTML( $error );
			} else {
				$request = $this->context->getRequest();
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
		$this->output->addHTML( Html::openElement( 'div',
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
		$this->output->addHTML( "</div>" );
	}

	protected function redirect( Workflow $workflow, $action = 'view', array $query = array() ) {
		$url = $this->urlGenerator->generateUrl( $workflow, $action, $query );
		$this->output->redirect( $url );
	}
}
