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

		$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $this->getModifiedRequest() );
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

			foreach( $commitResults as $key => $value ) {
				$output[$action]['result'][$key] = $this->processCommitResult( $value, $this->doRender() );
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

	protected function processCommitResult( $result, $render = true ) {
		$container = $this->getContainer();
		$templating = $container['templating'];
		$output = array();
		foreach( $result as $key => $value ) {
			if ( $value instanceof UUID ) {
				$output[$key] = $value->getAlphadecimal();
			} elseif ( $key === 'render-function' ) {
				if ( $render ) {
					$function = $value;
					$output['rendered'] = $function( $templating );
				}
			} else {
				$output[$key] = $value;
			}
		}

		return $output;
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
