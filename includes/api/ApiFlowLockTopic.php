<?php

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
			),
		);
	}

	public function getParamDescription() {
		return array(
			'moderationState' => "State to put topic in, either locked or unlocked",
			'reason' => 'Reason for locking or unlocking the topic',
		);
	}

	public function getDescription() {
		return 'Lock or unlock a Flow topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=lock-topic&cotmoderationState=lock&cotsummary=Ahhhh&workflow=',
		);
	}
}
