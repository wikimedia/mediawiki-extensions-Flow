<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowUndoEditHeader extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'ueh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'header' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'undo-edit-header';
	}

	public function getAllowedParams() {
		return [
			'startId' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'endId' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
		] + parent::getAllowedParams();
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=undo-edit-header&page=Talk:Sandbox&uehstartId=???&uehendId=???'
				=> 'apihelp-flow+undo-edit-header-example-1',
		];
	}
}
