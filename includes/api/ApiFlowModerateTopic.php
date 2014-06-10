<?php

class ApiFlowModerateTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'mt' );
	}

	protected function getBlockNames() {
		return array( 'topic' );
	}

	protected function getAction() {
		return 'moderate-topic';
	}

	public function getAllowedParams() {
		return array(
			'moderationState' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $this->getModerationStates(),
			),
			'reason' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'moderationState' => 'What level to moderate at',
			'reason' => 'Reason for moderation',
		);
	}

	public function getDescription() {
		return 'Moderates a Flow topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=moderate-topic&mtmoderationState=delete&mtreason=Ahhhh&workflow=',
		);
	}
}
