<?php

class ApiFlowViewTopic extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vt' );
	}

	/**
	 * Taken from ext.flow.base.js
	 *
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-topic';
	}

	public function getAllowedParams() {
		return array(
			'no-children' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'no-children' => 'If set, this won\'t render replies to the requested topic',
		);
	}

	public function getDescription() {
		return 'View a topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view-topic&workflow=',
		);
	}
}
