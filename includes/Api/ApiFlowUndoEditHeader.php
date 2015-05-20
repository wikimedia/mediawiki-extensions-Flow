<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowUndoEditHeader extends ApiFlowBaseGet {
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
		return 'undo-edit-header';
	}

	public function getAllowedParams() {
		return array(
			'startId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'endId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=undo-edit-header&page=Talk:Sandbox&uehstartId=???&uehendId=???'
				=> 'apihelp-flow+undo-edit-header-example-1',
		);
	}
}
