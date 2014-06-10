<?php

class ApiFlowEditHeader extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'eh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'header' );
	}

	protected function getAction() {
		return 'edit-header';
	}

	public function getAllowedParams() {
		return array(
			'prev_revision' => array(
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current header revision to check for edit conflicts',
			'content' => 'Content for header',
		);
	}

	public function getDescription() {
		return 'Edits a topic\'s header';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-header&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you&workflow=',
		);
	}
}
