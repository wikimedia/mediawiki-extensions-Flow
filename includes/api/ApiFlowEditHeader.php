<?php

class ApiFlowEditHeader extends ApiFlowBase {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'eh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return string
	 */
	protected function getBlockName() {
		return 'header';
	}

	protected function getAction() {
		return 'edit-header';
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
			'prev_revision' => '', // @todo what is this?
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