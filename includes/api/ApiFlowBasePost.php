<?php

use Flow\Block\AbstractBlock;
use Flow\Container;
use Flow\Model\Anchor;

abstract class ApiFlowBasePost extends ApiFlowBase {
	public function execute() {
		$loader = $this->getLoader();
		$blocks = $loader->createBlocks();
		/** @var \Flow\Model\Workflow $workflow */
		$workflow = $loader->getWorkflow();
		$action = $this->getAction();
		$user = $this->getUser();

		/** @var AbstractBlock $block */
		foreach ( $blocks as $block ) {
			$block->init( $action, $user );
		}
		$result = $this->getResult();

		$params = $this->getBlockParams();
		$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $params );

		// See if any of the blocks generated an error (in which case the
		// request will terminate with an the error message)
		$this->processError( $blocks );

		// If nothing was committed, we'll consider that an error (at least some
		// block should've been able to process the POST request)
		if ( !count( $blocksToCommit ) ) {
			$this->getResult()->dieUsage(
				wfMessage( 'flow-error-no-commit' )->parse(),
				'no-commit',
				200,
				array()
			);
		}

		$loader->commit( $workflow, $blocksToCommit );
		$savedBlocks = array();
		$result->setIndexedTagName( $savedBlocks, 'block' );

		foreach( $blocksToCommit as $block ) {
			$savedBlocks[] = $block->getName();
		}

		$output[$action] = array(
			'result' => array(),
			'status' => 'ok',
			'workflow' => $workflow->isNew() ? '' : $workflow->getId()->getAlphadecimal(),
		);

		foreach( $blocksToCommit as $block ) {
			// Always return parsed text to client after successful submission?
			// @Todo - hacky, maybe have contentformat in the request to overwrite
			// requiredWikitext
			$block->unsetRequiresWikitext( $action );
			$output[$action]['result'][$block->getName()] = $block->renderAPI( $params[$block->getName()] );
		}

		// required until php5.4 which has the JsonSerializable interface
		array_walk_recursive( $output, function( &$value ) {
			if ( $value instanceof Anchor ) {
				$value = $value->toArray();
			} elseif ( $value instanceof Message ) {
				$value = $value->text();
			}
		} );

		$this->getResult()->addValue( null, $this->apiFlow->getModuleName(), $output );
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}
}
