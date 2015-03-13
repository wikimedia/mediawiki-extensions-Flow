<?php

namespace Flow\Api;

use ApiBase;
use Flow\Model\Anchor;
use Flow\Model\UUID;
use Message;

abstract class ApiFlowBasePost extends ApiFlowBase {
	public function execute() {
		$loader = $this->getLoader();
		$blocks = $loader->getBlocks();
		/** @var \Flow\Model\Workflow $workflow */
		$workflow = $loader->getWorkflow();
		$action = $this->getAction();

		$result = $this->getResult();
		$params = $this->getBlockParams();
		$blocksToCommit = $loader->handleSubmit(
			$this->getContext(),
			$action,
			$params
		);

		// See if any of the blocks generated an error (in which case the
		// request will terminate with an the error message)
		$this->processError( $blocks );

		// If nothing was committed, we'll consider that an error (at least some
		// block should've been able to process the POST request)
		if ( !count( $blocksToCommit ) ) {
			$this->dieUsage(
				wfMessage( 'flow-error-no-commit' )->parse(),
				'no-commit',
				200,
				array()
			);
		}

		$commitMetadata = $loader->commit( $blocksToCommit );
		$savedBlocks = array();
		$result->setIndexedTagName( $savedBlocks, 'block' );

		foreach( $blocksToCommit as $block ) {
			$savedBlocks[] = $block->getName();
		}

		$output = array( $action => array(
			'status' => 'ok',
			'workflow' => $workflow->isNew() ? '' : $workflow->getId()->getAlphadecimal(),
			'committed' => $commitMetadata,
		) );

		// User frontends need this data, but bots do not.  When they
		// pass metadataonly=1 we will skip this data and return a slimmer
		// response in a shorter timeframe.
		if ( !$this->getParameter( 'metadataonly' ) ) {
			$output[$action]['result'] = array();
			foreach( $blocksToCommit as $block ) {
				// Always return parsed text to client after successful submission?
				// @Todo - hacky, maybe have contentformat in the request to overwrite
				// requiredWikitext
				$block->unsetRequiresWikitext( $action );
				$output[$action]['result'][$block->getName()] = $block->renderApi( $params[$block->getName()] );
			}
		}

		// required until php5.4 which has the JsonSerializable interface
		array_walk_recursive( $output, function( &$value ) {
			if ( $value instanceof Anchor ) {
				$value = $value->toArray();
			} elseif ( $value instanceof Message ) {
				$value = $value->text();
			} elseif ( $value instanceof UUID ) {
				$value = $value->getAlphadecimal();
			}
		} );

		$this->getResult()->addValue( null, $this->apiFlow->getModuleName(), $output );
	}

	public function getAllowedParams() {
		return array(
			'metadataonly' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
				ApiBase::PARAM_REQUIRED => false,
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function mustBePosted() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isWriteMode() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTokenSalt() {
		return '';
	}
}
