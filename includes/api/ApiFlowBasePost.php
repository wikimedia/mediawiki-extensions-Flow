<?php

use Flow\Model\UUID;
use Flow\Block\AbstractBlock;

abstract class ApiFlowBasePost extends ApiFlowBase {
	public function execute() {
		$loader = $this->getLoader();
		$blocks = $loader->createBlocks();
		/** @var \Flow\Model\Workflow $workflow */
		$workflow = $loader->getWorkflow();
		$action = $this->getAction();
		$controller = $this->getOccupationController();
		$user = $this->getUser();

		$article = new Article( $workflow->getArticleTitle(), 0 );

		$isNew = $workflow->isNew();
		// Is this making unnecessary db round trips?
		if ( !$isNew ) {
			$controller->ensureFlowRevision( $article );
		}

		/** @var AbstractBlock $block */
		foreach ( $blocks as $block ) {
			$block->init( $action, $user );
		}
		$result = $this->getResult();

		$request = $this->getModifiedRequest();
		$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );
		if ( count( $blocksToCommit ) ) {
			$commitResults = $loader->commit( $workflow, $blocksToCommit );
			$savedBlocks = array();
			$result->setIndexedTagName( $savedBlocks, 'block' );

			foreach( $blocksToCommit as $block ) {
				$savedBlocks[] = $block->getName();
			}

			$output[$action] = array(
				'result' => array(),
				'status' => 'ok',
			);

			$parameters = $loader->extractBlockParameters( $request, $blocksToCommit );
			foreach( $blocksToCommit as $block ) {
				$output[$action]['result'][$block->getName()] = $block->renderAPI( \Flow\Container::get( 'templating' ), $parameters[$block->getName()] );
			}
			if ( $isNew && !$workflow->isNew() ) {
				// Workflow was just created, ensure its underlying page is owned by flow
				$controller->ensureFlowRevision( $article );
			}
		} else {
			$output[$action] = array(
				'status' => 'error',
				'result' => array(),
			);

			foreach( $blocks as $block ) {
				if ( $block->hasErrors() ) {
					$errors = $block->getErrors();
					$nativeErrors = array();

					foreach( $errors as $key ) {
						$nativeErrors[$key]['message'] = $block->getErrorMessage( $key )->parse();
						$nativeErrors[$key]['extra'] = $block->getErrorExtra( $key );
					}

					$output[$action]['result'][$block->getName()] = $nativeErrors;
				}
			}
		}

		$this->getResult()->addValue( null, $this->apiFlow->getModuleName(), $output );
	}

	public function mustBePosted() {
		return true;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return true;
	}
}
