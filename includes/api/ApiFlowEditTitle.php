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
			'format' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 'wikitext',
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current header revision to check for edit conflicts',
			'content' => 'Content for title',
			'format' => 'Format of the content (wikitext|html)',
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
			'api.php?action=flow&submodule=edit-title&page=Topic:S2tycnas4hcucw8w&ehprev_revision=???&ehtcontent=Nice%20to&20meet%20you&ehtformat=wikitext',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-title&page=Topic:S2tycnas4hcucw8w&ehprev_revision=???&ehtcontent=Nice%20to&20meet%20you&ehtformat=wikitext'
				=> 'apihelp-flow+edit-title-example-1',
		);
	}
}
