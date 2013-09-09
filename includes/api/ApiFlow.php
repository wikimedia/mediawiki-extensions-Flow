<?php

use Flow\Model\UUID;

class ApiFlow extends ApiBase {
	public function execute() {
		$this->container = Flow\Container::getContainer();
		$params = $this->extractRequestParams();
		$output = array();

		if ( ! $params['workflow'] && ! $params['page'] ) {
			$this->dieUsage( 'missing-param', 'One of workflow or page parameters must be provided' );
			return;
		}

		$id = UUID::create( $params['workflow'] );
		$page = false;

		if ( $params['page'] ) {
			$page = Title::newFromText( $params['page'] );
		}

		$this->loader = $this->container['factory.loader.workflow']
			->createWorkflowLoader( $page, $id );

		$requestParams = json_decode( $params['params'], true );
		$request = new DerivativeRequest( $this->getContext()->getRequest(), $requestParams, true );

		$blocks = $this->loader->createBlocks();
		$action = $params['flowaction'];
		$user = $this->getContext()->getUser();

		foreach( $blocks as $block ) {
			$block->init( $action, $user );
		}

		$blocksToCommit = $this->loader->handleSubmit( $action, $blocks, $user, $request );
		if ( $blocksToCommit ) {
			$commitResults = $this->loader->commit( $this->loader->getWorkflow(), $blocksToCommit );

			$savedBlocks = array( '_element' => 'block' );

			foreach( $blocksToCommit as $block ) {
				$savedBlocks[] = $block->getName();
			}

			$output[$action] = array(
				'result' => array(),
			);

			$doRender = ( $params['render'] != false );

			foreach( $commitResults as $key => $value ) {
				$output[$action]['result'][$key] = $this->processCommitResult( $value, $doRender );
			}
		} else {
			$output[$action] = array(
				'result' => 'error',
				'errors' => array(),
			);

			foreach( $blocks as $block ) {
				if ( $block->hasErrors() ) {
					$errors = $block->getErrors();
					$nativeErrors = array();

					foreach( $errors as $key => $error ) {
						$nativeErrors[$key] = $error->plain();
					}

					$output[$action]['errors'][$block->getName()] = $nativeErrors;
				}
			}
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
		return true;
	}

	protected function processCommitResult( $result, $render = true ) {
		$templating = $this->container['templating'];
		$output = array();
		foreach( $result as $key => $value ) {
			if ( $value instanceof UUID ) {
				$output[$key] = $value->getHex();
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

	protected function doRerender( $blocks ) {
		$templating = $this->container['templating'];

		$output = array();

		$output['count'] = count( $blocks );

		foreach( $blocks as $block ) {
			$output[$block->getName()] = $block->render( $templating, array() );
		}

		return $output;
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
				ApiBase::PARAM_DFLT => null,
			),
			'page' => null,
			'params' => array(
				ApiBase::PARAM_DFLT => '{}',
			),
			'token' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'render' => array(
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'action' => 'The action to take',
			'workflow' => 'The Workflow to take an action on',
			'params' => 'The parameters to pass',
			'token' => 'A token retrieved from api.php?action=tokens&type=flow.',
			'render' => 'Set this to something to include a block-specific rendering in the output',
		);
	}

	public function getExamples() {
		// @todo: fill out an example
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
		global $wgFlowTokenSalt;
		return $wgFlowTokenSalt;
	}
}
