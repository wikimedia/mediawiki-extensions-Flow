<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowViewHeader extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'header' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'view-header';
	}

	public function getAllowedParams() {
		return [
			'format' => [
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext', 'fixed-html' ],
				ParamValidator::PARAM_DEFAULT => 'fixed-html',
			],
			'revId' => null,
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=view-header&page=Talk:Sandbox&vhformat=wikitext&revId='
				=> 'apihelp-flow+view-header-example-1',
		];
	}
}
