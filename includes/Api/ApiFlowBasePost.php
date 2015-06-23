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

		// If nothing is ready to be committed, we'll consider that an error (at least some
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

		// We used to provide render data along with these POST APIs because we
		// needed them to render JS. Now we have view-* API's and JS is using
		// them - we don't need this hack anymore.
		// We'll let this live on for a little while and warn users who were not
		// already requesting only metadata that this is soon changing.
		if ( $this->getParameter( 'metadataonly' ) !== true ) {
			$output[$action]['result'] = array();
			foreach( $blocksToCommit as $block ) {
				// Always return parsed text to client after successful submission?
				$output[$action]['result'][$block->getName()] = $block->renderApi( $params[$block->getName()] );
			}

			$this->setWarning(
				'The API will soon stop providing detailed render data in ' .
				'flow.[action].result. The metadataonly option will be ' .
				'removed, and the API will always only provide metadata. ' .
				'It will behave as it currently does when metadataonly is true. ' .
				'Start getting render data from view-* API submodules.'
			);
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
				// going to deprecate this (it's becoming default behavior) and
				// I want to warn people who DON'T set this param, so I don't
				// want it to default to anything (so I can check for null)
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
