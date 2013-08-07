<?php

use Flow\WorkflowLoader;
use Flow\Model\UUID;

class ApiQueryFlow extends ApiQueryBase {
	protected $loader, $workflow, $definition;

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'flow' );
	}

	public function execute() {
		$this->container = include dirname(__FILE__) . "/../../container.php";
		// Get the parameters
		$params = $this->extractRequestParams();

		$pageTitle = Title::newFromText( $params['page'] );
		$id = $params['workflow'] ? new UUID( $params['workflow'] ) : null;

		$this->loader = new WorkflowLoader( $pageTitle, $id );

		$blocks = $this->loader->createBlocks();
		$blockOutput = array();
		foreach( $blocks as $block ) {
			$block->init( $params['action'] );

			$blockOutput[] = $block->renderAPI( $params ) +
				array( 'block-name' => $block->getName() );
		}

		$result = array(
			'_element' => 'block',
		) + $blockOutput;

		$this->getResult()->addValue( 'query', $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return array(
			'workflow' => array(
				// ApiBase::PARAM_ISMULTI => true,
			),
			'page' => array(
				// ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_REQUIRED => true,
			),
			'action' => array(
				ApiBase::PARAM_DFLT => 'view',
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
			'api.php?action=query&meta=',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Flow_Portal';
	}

	public function getVersion() {
		return __CLASS__ . '-0.1';
	}
}