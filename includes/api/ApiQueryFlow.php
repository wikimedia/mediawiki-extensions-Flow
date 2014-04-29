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

		$blocks = $this->loader->createBlocks();
		$blockOutput = array();
		foreach( $blocks as $block ) {
			$block->init( $params['action'], $this->getUser() );

			$blockParams = array();
			if ( isset( $passedParams[$block->getName()] ) ) {
				$blockParams = $passedParams[$block->getName()];
			}

			$templating = $container['templating'];

			if ( $block->canRender( $params['action'] ) ) {
				$thisBlock = $block->renderAPI( $templating, $blockParams ) +
					array(
						'block-name' => $block->getName()
					);

				$blockOutput[] = $thisBlock;
			}
		}

		$result = array(
			'element' => 'block',
			'workflow-id' => $this->loader->getWorkflow()->getId()->getAlphadecimal(),
		) + $blockOutput;
		$this->getResult()->setIndexedTagName( $result, 'block' );

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
}
