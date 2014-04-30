<?php

use Flow\Model\UUID;

class ApiQueryFlow extends ApiQueryBase {
	protected $loader, $workflow, $definition, $container;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'flow' );
	}

	/**
	 * @return \Flow\Container
	 */
	private function getContainer() {
		if ( !$this->container ) {
			$this->container = Flow\Container::getContainer();
		}
		return $this->container;
	}

	public function execute() {
		$container = $this->getContainer();
		// Get the parameters
		$params = $this->extractRequestParams();
		$passedParams = FormatJson::decode( $params['params'], true );

		$pageTitle = Title::newFromText( $params['page'] );
		$id = $params['workflow'] ? UUID::create( $params['workflow'] ) : null;

		$this->loader = $container['factory.loader.workflow']
			->createWorkflowLoader( $pageTitle, $id );
		$result = array(
			'workflow' => $this->loader->getWorkflow()->getId()->getAlphadecimal()
		);
		foreach( $this->loader->createBlocks() as $block ) {
			$block->init( $params['action'], $this->getUser() );

			$blockParams = array();
			if ( isset( $passedParams[$block->getName()] ) ) {
				$blockParams = $passedParams[$block->getName()];
			}

			$templating = $container['templating'];

			if ( $block->canRender( $params['action'] ) ) {
				$result['blocks'][] = $block->renderAPI( $templating, $blockParams );
			}
		}

<<<<<<< HEAD   (49e2e0 Fix missing edit token)
		array_walk_recursive( $result, function( &$value ) {
			// This is required untill php 5.4.0 after which we can
			// implement the JsonSerializable interface for Message
			if ( $value instanceof Message ) {
				$value = $value->text();
			}
		} );
=======
		$result = array(
			'element' => 'block',
			'workflow-id' => $this->loader->getWorkflow()->getId()->getAlphadecimal(),
		) + $blockOutput;
		$this->getResult()->setIndexedTagName( $result, 'block' );

>>>>>>> BRANCH (9e19ba Localisation updates from https://translatewiki.net.)
		$this->getResult()->addValue( 'query', $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return array(
			'workflow' => array(
			),
			'page' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'action' => array(
				ApiBase::PARAM_DFLT => 'view',
				ApiBase::PARAM_TYPE => $this->getReadOnlyFlowActions(),
			),
			'params' => array(
				ApiBase::PARAM_DFLT => '{}',
			),
		);
	}

	private function getReadOnlyFlowActions() {
		$container = $this->getContainer();
		/** @var \Flow\FlowActions $flowActions */
		$flowActions = $container['flow_actions'];
		$readOnly = array();
		foreach( $flowActions->getActions() as $action ) {
			if ( !$flowActions->getValue( $action, 'performs-writes' ) ) {
				$readOnly[] = $action;
			}
		}

		return $readOnly;
	}

	public function getDescription() {
		return 'Shim to query to the internal Flow API.  This API is not suggested ' .
			'for external use and will soon be superseded by an integrated mediawiki api.';
	}


	public function getParamDescription() {
		return array(
			'workflow' => 'Hex-encoded ID of the workflow to query',
			'page' => 'Title of the page to query',
			'action' => 'The view-type action to take',
			'params' => 'View parameters to pass to each block, indexed by block name',
		);
	}

	public function getExamples() {
		return array(
			'api.php?action=query&list=flow&flowpage=Main_Page',
		);
	}

	static public function array_merge_array( array $arrays ) {
		switch( count( $arrays ) ) {
		case 0:
			return array();

		case 1:
			return reset( $arrays );

		default:
			return call_user_func_array( 'array_merge', $arrays );
		}

	}
}
