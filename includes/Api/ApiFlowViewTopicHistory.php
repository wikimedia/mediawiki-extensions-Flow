<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowViewTopicHistory extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vth' );
	}

	/**
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'topic' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'view-topic-history';
	}

	public function getAllowedParams() {
		return [
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
			'action=flow&submodule=view-topic-history&page=Topic:S2tycnas4hcucw8w&vthformat=wikitext'
				=> 'apihelp-flow+view-topic-history-example-1',
		];
	}
}
