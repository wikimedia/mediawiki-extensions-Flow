<?php

class ApiFlowModeratePost extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'mp' );
	}

	protected function getBlockNames() {
		return array( 'topic' );
	}

	protected function getAction() {
		return 'moderate-post';
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
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'moderationState' => 'What level to moderate at',
			'reason' => 'Reason for moderation',
			'postId' => 'Id of post to moderate',
		);
	}

	public function getDescription() {
		return 'Moderates a Flow post';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=moderate-post&mppostId=050f30e34c87beebcd54080027630f57&mpmoderationState=delete&mpreason=Ahhhh&workflow=',
		);
	}
}
