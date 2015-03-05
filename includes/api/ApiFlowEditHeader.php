<?php

class ApiFlowEditHeader extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'eh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'header' => $this->extractRequestParams() );
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
			'format' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 'wikitext',
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current header revision to check for edit conflicts',
			'content' => 'Content for header',
			'format' => 'Format of the content (wikitext|html)',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Edits a topic\'s header';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-header&page=Talk:Sandbox&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you&ehformat=wikitext',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-header&page=Talk:Sandbox&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you&ehformat=wikitext'
				=> 'apihelp-flow+edit-header-example-1',
		);
	}
}
