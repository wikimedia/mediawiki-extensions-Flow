<?php

namespace Flow\Api;

use ApiBase;

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
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-header&page=Talk:Sandbox&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you&ehformat=wikitext'
				=> 'apihelp-flow+edit-header-example-1',
		);
	}
}
