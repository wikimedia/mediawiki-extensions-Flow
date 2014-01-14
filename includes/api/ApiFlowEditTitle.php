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
			'prev_revision' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current header revision to check for edit conflicts',
			'content' => 'Content for title',
		);
	}

	public function getDescription() {
		return 'Edits a topic\'s title';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-title&ehprev_revision=???&ehtcontent=Nice%20to&20meet%20you&workflow=',
		);
	}
}
