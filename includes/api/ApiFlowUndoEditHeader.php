<?php

class ApiFlowUndoEditHeader extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'ueh' );
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
			'startId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'endId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
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
			'startId' => 'Revision id to start undo at',
			'endId' => 'Revision id to end undo at',
			'prev_revision' => 'Revision id of the current header revision to check for edit conflicts',
			'content' => 'Content for header',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Undoes an edit to a topic\'s header';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array_keys( $this->getExamplesMessages() );
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=undo-edit-header&page=Talk:Sandbox&uehstartId=???&uehendId=???&uehprev_revision=???&uehcontent=Nice%20to&20meet%20you'
				=> 'apihelp-flow+undo-edit-header-example-1',
		);
	}
}
