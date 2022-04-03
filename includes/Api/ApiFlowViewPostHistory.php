<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowViewPostHistory extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vph' );
	}

	/**
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'topic' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'view-post-history';
	}

	public function getAllowedParams() {
		return [
			'postId' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'format' => [
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext', 'fixed-html' ],
				ParamValidator::PARAM_DEFAULT => 'fixed-html',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=view-post-history&page=Topic:S2tycnas4hcucw8w&vphpostId=???&vphformat=wikitext'
				=> 'apihelp-flow+view-post-history-example-1',
		];
	}
}
