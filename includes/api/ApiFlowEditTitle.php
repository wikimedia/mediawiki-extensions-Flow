<?php

class ApiFlowEditTitle extends ApiFlowBase {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'et' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return string
	 */
	protected function getBlockName() {
		return 'topic';
	}

	protected function getAction() {
		return 'edit-title';
	}

	public function getAllowedParams() {
		return array(
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'content' => 'Content for title',
		);
	}

	public function getDescription() {
		return 'Edits a topic\'s title';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-title&ehtcontent=Nice%20to&20meet%20you&workflow=',
		);
	}

}