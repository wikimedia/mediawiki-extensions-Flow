<?php

class ApiFlowEditTitle extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'et' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
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
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current title revision to check for edit conflicts',
			'content' => 'Content for title',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Edits a topic\'s title';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-title&page=Topic:S2tycnas4hcucw8w&etprev_revision=???&etcontent=Nice%20to&20meet%20you',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-title&page=Topic:S2tycnas4hcucw8w&etprev_revision=???&ehtcontent=Nice%20to&20meet%20you'
				=> 'apihelp-flow+edit-title-example-1',
		);
	}
}
