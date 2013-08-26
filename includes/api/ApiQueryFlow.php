<?php

use Flow\WorkflowLoader;
use Flow\Model\UUID;

class ApiQueryFlow extends ApiQueryBase {
	protected $loader, $workflow, $definition;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'flow' );
	}

	public function execute() {
		$this->container = Flow\Container::getContainer();
		// Get the parameters
		$params = $this->extractRequestParams();
		$passedParams = json_decode( $params['params'], true );

		$pageTitle = Title::newFromText( $params['page'] );
		$id = $params['workflow'] ? UUID::create( $params['workflow'] ) : null;

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

			$templating = $this->container['templating'];
			$thisBlock = $block->renderAPI( $templating, $blockParams ) +
				array(
					'block-name' => $block->getName()
				);

			$templating = $this->container['templating'];

			$blockOutput[] = $thisBlock;
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
			'page' => 'Title of the page to query',
			'action' => 'The view-type action to take',
			'params' => 'View parameters to pass to each block, indexed by block name',
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
