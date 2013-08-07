<?php

use Flow\Model\UUID;

class ApiFlow extends ApiBase {
	public function execute() {
		$this->container = include __DIR__ . "/../../container.php";
		$params = $this->extractRequestParams();
		$output = array();

		if ( $params['gettoken'] ) {
			$this->getResult()->addValue( null, $this->getModuleName(), array(
				'token' => $this->getContext()->getUser()->getEditToken( 'flow' ),
			) );
			return true;
		}

		$id = UUID::create( $params['workflow'] );

		$this->loader = $this->container['factory.loader.workflow']
			->createWorkflowLoader( false, $id );

		$requestParams = json_decode( $params['params'], true );
		$request = new DerivativeRequest( $this->getContext()->getRequest(), $requestParams, true );

		$blocks = $this->loader->createBlocks();
		$action = $params['flowaction'];
		$user = $this->getContext()->getUser();

		foreach( $blocks as $block ) {
			$block->init( $action );
		}

		$blocksToCommit = $this->loader->handleSubmit( $action, $blocks, $user, $request );
		if ( $blocksToCommit ) {
			$this->loader->commit( $this->loader->getWorkflow(), $blocksToCommit );

			$savedBlocks = array( '_element' => 'block' );

			foreach( $blocksToCommit as $block ) {
				$savedBlocks[] = $block->getName();
			}

			$output[$action] = array(
				'result' => 'success',
				'saved-blocks' => $savedBlocks,
			);
		} else {
			$output[$action] = array(
				'result' => 'nop',
			);
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		return true;
	}

	public function getDescription() {
		 return 'Allows actions to be taken on Flow Workflows';
	}

	public function getAllowedParams() {
		return array(
			'flowaction' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'workflow' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'params' => array(
				ApiBase::PARAM_DFLT => '{}',
			),
			'token' => null,
			'gettoken' => array(
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'action' => 'The action to take',
			'workflow' => 'The Workflow to take an action on',
			'params' => 'The parameters to pass',
			'token' => 'A token retrieved by calling this '
		);
	}

	public function getExamples() {
		 return array(
			 ''
			 => ''
		 );
	}

	public function mustBePosted() {
		return true;
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		return 'flow';
	}
}