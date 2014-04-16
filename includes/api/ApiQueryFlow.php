<?php

use Flow\Model\UUID;

class ApiQueryFlow extends ApiQueryBase {
	protected $loader, $workflow, $definition, $container;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'flow' );
	}

	public function execute() {
		$this->container = Flow\Container::getContainer();
		// Get the parameters
		$params = $this->extractRequestParams();
		$passedParams = FormatJson::decode( $params['params'], true );

		$pageTitle = Title::newFromText( $params['page'] );
		$id = $params['workflow'] ? UUID::create( $params['workflow'] ) : null;

		$this->loader = $this->container['factory.loader.workflow']
			->createWorkflowLoader( $pageTitle, $id );

		$blocks = $this->loader->createBlocks();
		$blockOutput = array();
		foreach( $blocks as $block ) {
			$block->init( $params['action'], $this->getUser() );

			$blockParams = array();
			if ( isset( $passedParams[$block->getName()] ) ) {
				$blockParams = $passedParams[$block->getName()];
			}

			$templating = $this->container['templating'];

			if ( $block->canRender( $params['action'] ) ) {
				$thisBlock = $block->renderAPI( $templating, $this->getResult(), $blockParams ) +
					array(
						'block-name' => $block->getName()
					);

				$blockOutput[] = $thisBlock;
			}
		}

		$result = array(
			'_element' => 'block',
			'workflow-id' => $this->loader->getWorkflow()->getId()->getAlphadecimal(),
		) + $blockOutput;

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
			),
			'params' => array(
				ApiBase::PARAM_DFLT => '{}',
			),
		);
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
