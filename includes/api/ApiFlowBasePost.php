<?php

use Flow\Anchor;
use Flow\Block\AbstractBlock;
use Flow\Container;

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

		$request = $this->getModifiedRequest();
		$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $request );

		// If none of the blocks could process this submission, fail
		if ( !count( $blocksToCommit ) ) {
			$this->processError( $blocks );
			return;
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

		$parameters = $loader->extractBlockParameters( $request, $blocksToCommit );
		foreach( $blocksToCommit as $block ) {
			// Always return parsed text to client after successful submission?
			// @Todo - hacky, maybe have contentformat in the request to overwrite
			// requiredWikitext
			$block->unsetRequiresWikitext( $action );
			$output[$action]['result'][$block->getName()] = $block->renderAPI( Container::get( 'templating' ), $parameters[$block->getName()] );
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

	/**
	 * Kill the request if errors were encountered.
	 * Only the first error will be output:
	 * * dieUsage only outputs one error - we could add more as $extraData, but
	 *   that would mean we'd have to check for flow-specific errors differently
	 * * most of our code just quits on the first error that's encountered, so
	 *   outputting all encountered errors might still not cover everything
	 *   that's wrong with the request
	 *
	 * @param array $blocks
	 */
	protected function processError( $blocks ) {
		foreach( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				foreach( $errors as $key ) {
					$this->getResult()->dieUsage(
						$block->getErrorMessage( $key )->parse(),
						$key,
						200,
						// additional info for this message (e.g. to be used to
						// enable recovery from error, like returning the most
						// recent revision ID to re-submit content in the case
						// of edit conflict)
						array( $key => $block->getErrorExtra( $key ) )
					);
				}
			}
		}
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
