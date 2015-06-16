<?php

namespace Flow\Api;

use ApiBase;
use Flow\Model\AbstractRevision;

class ApiFlowLockTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'cot' );
	}

	protected function getBlockParams() {
		$params = $this->extractRequestParams();
		return array(
			'topic' => $params,
		);
	}

	protected function getAction() {
		return 'lock-topic';
	}

	public function isDeprecated() {
		return $this->getModuleName() === 'close-open-topic';
	}

	public function getAllowedParams() {
		return array(
			'moderationState' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => array(
					AbstractRevision::MODERATED_LOCKED, 'unlock',
					'close', 'reopen' // BC: now replaced by lock & unlock
				),
			),
			'reason' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string',
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'moderationState' => "State to put topic in, either locked or unlocked",
			'reason' => 'Reason for locking or unlocking the topic',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Lock or unlock a Flow topic';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=lock-topic&page=Topic:S2tycnas4hcucw8w&cotmoderationState=lock&cotreason=Ahhhh',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=lock-topic&page=Topic:S2tycnas4hcucw8w&cotmoderationState=lock&cotreason=Ahhhh'
				=> 'apihelp-flow+lock-topic-example-1',
		);
	}
}
