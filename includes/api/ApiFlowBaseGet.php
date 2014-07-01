<?php

use Flow\Anchor;
use Flow\Block\AbstractBlock;

abstract class ApiFlowBaseGet extends ApiFlowBase {
	public function execute() {
		$loader = $this->getLoader();
		$blocks = $loader->createBlocks();
		$action = $this->getAction();
		$user = $this->getUser();
		$container = $this->getContainer();
		$templating = $container['templating'];
		$passedParams = $loader->extractBlockParameters($this->getModifiedRequest(), $blocks);

		$output[$action] = array(
			'result' => array(),
			'status' => 'ok',
		);

		/** @var AbstractBlock $block */
		foreach( $blocks as $block ) {
			$block->init( $action, $user );

			if ( $block->canRender( $action ) ) {
				$blockParams = array();
				if ( isset( $passedParams[$block->getName()] ) ) {
					$blockParams = $passedParams[$block->getName()];
				}

				$output[$action]['result'][$block->getName()] = $block->renderAPI( $templating, $blockParams );
			}
		}

		// See if any of the blocks generated an error (in which case the
		// request will terminate with an the error message)
		$this->processError( $blocks );

		// If nothing could render, we'll consider that an error (at least some
		// block should've been able to render a GET request)
		if ( !$output[$action]['result'] ) {
			$this->getResult()->dieUsage(
				wfMessage( 'flow-error-no-render' )->parse(),
				'no-render',
				200,
				array()
			);
		}

		$blocks = array_keys($output[$action]['result']);
		$this->getResult()->setIndexedTagName( $blocks, 'block' );

		// Required until php5.4 which has the JsonSerializable interface
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
		return false;
	}

	public function needsToken() {
		return false;
	}

	public function getTokenSalt() {
		return false;
	}
}
