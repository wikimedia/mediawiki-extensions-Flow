<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowModerateTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'mt' );
	}

	protected function getBlockParams() {
		return [ 'topic' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'moderate-topic';
	}

	public function getAllowedParams() {
		return [
			'moderationState' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => $this->getModerationStates(),
			],
			'reason' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
		] + parent::getAllowedParams();
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=moderate-topic&page=Topic:S2tycnas4hcucw8w&mtmoderationState=delete&mtreason=Ahhhh'
				=> 'apihelp-flow+moderate-topic-example-1',
		];
	}
}
