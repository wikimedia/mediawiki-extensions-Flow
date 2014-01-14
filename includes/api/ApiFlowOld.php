<?php

use Flow\Model\UUID;

class ApiFlowOld extends ApiBase {

	/** @var Flow\Container $container */
	protected $container;

	/** @var \Flow\WorkflowLoader $loader */
	protected $loader;

	public function execute() {
		$this->container = Flow\Container::getContainer();
		$params = $this->extractRequestParams();
		$output = array();

		if ( ! $params['workflow'] && ! $params['page'] ) {
			$this->dieUsage( 'One of workflow or page parameters must be provided', 'badparams' );
			return;
		}

		$id = UUID::create( $params['workflow'] );
		$page = false;
		if ( $params['page'] ) {
			$page = Title::newFromText( $params['page'] );
		}
		$this->loader = $this->container['factory.loader.workflow']
			->createWorkflowLoader( $page, $id );
		$occupationController = $this->container['occupation_controller'];
		$workflow = $this->loader->getWorkflow();
		$article = new Article( $workflow->getArticleTitle(), 0 );

		$isNew = $workflow->isNew();
		// Is this making unnecesary db round trips?
		if ( !$isNew ) {
			$occupationController->ensureFlowRevision( $article );
		}
		$requestParams = FormatJson::decode( $params['params'], true );

		if ( ! $requestParams ) {
			$this->dieUsage( 'The params parameter must be a valid JSON string', 'badparams' );
			return;
		}

		$request = new DerivativeRequest( $this->getRequest(), $requestParams, true );

		$blocks = $this->loader->createBlocks();
		$action = $params['flowaction'];
		$user = $this->getUser();

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
			if ( $isNew && !$workflow->isNew() ) {
				// Workflow was just created, ensure its underlying page is owned by flow
				$occupationController->ensureFlowRevision( $article );
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

					foreach( $errors as $key ) {
						$nativeErrors[$key]['message'] = $block->getErrorMessage( $key )->parse();
						$nativeErrors[$key]['extra'] = $block->getErrorExtra( $key );
					}

					$output[$action]['errors'][$block->getName()] = $nativeErrors;
				}
			}
		}

		$this->getResult()->addValue( null, 'flow', $output );
		$this->setWarning( 'This API is not suggested for external use and is superseded by an integrated mediawiki api.' );
		return true;
	}

	protected function processCommitResult( $result, $render = true ) {
		$templating = $this->container['templating'];
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

	protected function doRerender( $blocks ) {
		$templating = $this->container['templating'];

		$output = array();

		$output['count'] = count( $blocks );

		foreach( $blocks as $block ) {
			$output[$block->getName()] = $block->render( $templating, array() );
		}

		return $output;
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

	public function getDescription() {
		return 'Shim to perform actions against the internal Flow API. This API is not suggested '.
			'for external use and is superseded by an integrated mediawiki api.';
	}

	public function getParamDescription() {
		return array(
			'action' => 'The action to take',
			'workflow' => 'The Workflow to take an action on',
			'params' => 'The parameters to pass',
			'token' => 'A token retrieved from api.php?action=tokens&type=flow',
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
