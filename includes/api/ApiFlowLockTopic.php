<?php

use Flow\Model\AbstractRevision;

class ApiFlowLockTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'cot' );
	}

	protected function getBlockNames() {
		return array( 'topic', 'topicsummary' );
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
			'summary' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'prev_revision' => null,
		);
	}

	public function getParamDescription() {
		return array(
			'moderationState' => 'What level to moderate at',
			'summary' => 'Summary for closing/reopening topic',
			'prev_revision' => 'Revision id of the current topic summary revision to check for edit conflicts. Null for a new topic summary revision',
		);
	}

	public function getDescription() {
		return 'Lock or unlock a Flow topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=lock-topic&cotmoderationState=lock&cotsummary=Ahhhh&cotprev_revision=xjs&workflow=',
		);
	}
}
