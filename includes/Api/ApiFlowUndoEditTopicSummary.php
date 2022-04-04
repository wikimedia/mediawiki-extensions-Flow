<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowUndoEditTopicSummary extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'uets' );
	}

	protected function getAction() {
		return 'undo-edit-topic-summary';
	}

	protected function getBlockParams() {
		return [
			'topicsummary' => $this->extractRequestParams(),
			'topic' => [],
		];
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
			'action=flow&submodule=undo-edit-topic-summary&page=Topic:S2tycnas4hcucw8w&uetsstartId=???&uetsendId=???'
				=> 'apihelp-flow+undo-edit-topic-summary-example-1',
		];
	}
}
