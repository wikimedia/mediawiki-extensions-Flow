<?php

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

				$output[$action]['result'][$block->getName()] = $block->renderAPI( $templating, $this->getResult(), $blockParams );
			}
		}

		// if nothing could render, we'll consider that an error (at least some
		// block should've been able to render a GET request)
		if ( !$output[$action]['result'] ) {
			$output[$action] = array(
				'status' => 'error',
				'result' => array(
					'message' => 'No block was able to render the request',
					'extra' => array(),
				)
			);
		} else {
			$blocks = array_keys($output[$action]['result']);
			$this->getResult()->setIndexedTagName( $blocks, 'block' );
		}

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
