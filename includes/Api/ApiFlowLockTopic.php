<?php

namespace Flow\Api;

use Flow\Model\AbstractRevision;
use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowLockTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'cot' );
	}

	protected function getBlockParams() {
		$params = $this->extractRequestParams();
		return [
			'topic' => $params,
		];
	}

	protected function getAction() {
		return 'lock-topic';
	}

	public function isDeprecated() {
		return $this->getModuleName() === 'close-open-topic';
	}

	public function getAllowedParams() {
		return [
			'moderationState' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => [
					AbstractRevision::MODERATED_LOCKED, 'unlock',
					'close', 'reopen' // BC: now replaced by lock & unlock
				],
			],
			'reason' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string',
			],
		] + parent::getAllowedParams();
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=lock-topic&page=Topic:S2tycnas4hcucw8w&cotmoderationState=lock&cotreason=Ahhhh'
				=> 'apihelp-flow+lock-topic-example-1',
		];
	}
}
