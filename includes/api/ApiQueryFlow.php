<?php

use Flow\WorkflowLoader;
use Flow\Model\UUID;

class ApiQueryFlow extends ApiQueryBase {
	protected $loader, $workflow, $definition;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'flow' );
	}

	public function execute() {
		$this->container = include __DIR__ . "/../../container.php";
		// Get the parameters
		$params = $this->extractRequestParams();
		$passedParams = json_decode( $params['params'], true );

		$pageTitle = Title::newFromText( $params['page'] );
		$id = $params['workflow'] ? new UUID( $params['workflow'] ) : null;

		$this->loader = $this->container['factory.loader.workflow']
			->createWorkflowLoader( $pageTitle, $id );

		$blocks = $this->loader->createBlocks();
		$blockOutput = array();
		foreach( $blocks as $block ) {
			$block->init( $params['action'], $this->getContext()->getUser() );

			$blockParams = array();
			if ( isset($passedParams[$block->getName()]) ) {
				$blockParams = $passedParams[$block->getName()];
			}

			$blockOutput[] = $block->renderAPI( $blockParams ) +
				array( 'block-name' => $block->getName() );
		}

		$result = array(
			'_element' => 'block',
			'workflow-id' => $this->loader->getWorkflow()->getId()->getHex(),
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

	public function getParamDescription() {
		return array(
			'workflow' => 'Hex-encoded ID of the workflow to query',
			'page' => 'Title of the page to query'
		);
	}

	public function getDescription() {
		return 'Queries the Flow subsystem for data';
	}

	public function getExamples() {
		return array(
			'api.php?action=query&list=flow&flowpage=Talk:Main_Page',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Flow_Portal';
	}

	public function getVersion() {
		return __CLASS__ . '-0.1';
	}
}