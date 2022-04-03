<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowViewTopicSummary extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vts' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'topicsummary' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'view-topic-summary';
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
			'action=flow&submodule=view-topic-summary&page=Topic:S2tycnas4hcucw8w&vtsformat=wikitext&revId='
				=> 'apihelp-flow+view-topic-summary-example-1',
		];
	}
}
