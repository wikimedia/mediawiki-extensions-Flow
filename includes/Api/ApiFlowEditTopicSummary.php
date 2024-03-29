<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowEditTopicSummary extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-topic-summary', 'ets' );
	}

	protected function getAction() {
		return 'edit-topic-summary';
	}

	protected function getBlockParams() {
		return [
			'topicsummary' => $this->extractRequestParams(),
			'topic' => [],
		];
	}

	public function getAllowedParams() {
		return [
			'prev_revision' => null,
			'summary' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'format' => [
				ParamValidator::PARAM_DEFAULT => 'wikitext',
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext' ],
			],
		] + parent::getAllowedParams();
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=edit-topic-summary&page=Topic:S2tycnas4hcucw8w&wetsprev_revision=???' .
				'&etssummary=Nice%20to&20meet%20you&etsformat=wikitext'
				=> 'apihelp-flow+edit-topic-summary-example-1',
		];
	}
}
