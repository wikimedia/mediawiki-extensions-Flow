<?php

use Flow\Anchor;
use Flow\Model\UUID;

/**
 * This API is deprecated and should not be used. Instead use
 * ApiFlowView(Header|Post|Topic|TopicList|TopicSummary)
 *
 * @deprecated
 */
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
		$result = array(
			'workflow' => $this->loader->getWorkflow()->getId()->getAlphadecimal()
		);
		foreach( $this->loader->createBlocks() as $block ) {
			$block->init( $params['action'], $this->getUser() );

			$blockParams = array();
			if ( isset( $passedParams[$block->getName()] ) ) {
				$blockParams = $passedParams[$block->getName()];
			}

			$templating = $this->container['templating'];

			if ( $block->canRender( $params['action'] ) ) {
				$result['blocks'][] = $block->renderAPI( $templating, $blockParams );
			}
		}

		array_walk_recursive( $result, function( &$value ) {
			// This is required untill php 5.4.0 after which we can
			// implement the JsonSerializable interface for Anchor
			if ( $value instanceof Anchor ) {
				$value = $value->toArray();
			}
		} );

		$this->getResult()->addValue( 'query', $this->getModuleName(), $result );
		$this->setWarning( 'This API is deprecated and should not be used. Instead use ApiFlowView(Header|Post|Topic|TopicList|TopicSummary).' );
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
