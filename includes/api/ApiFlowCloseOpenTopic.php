<?php

use Flow\Model\AbstractRevision;

class ApiFlowCloseOpenTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'cot' );
	}

	protected function getBlockNames() {
		return array( 'topic', 'topicsummary' );
	}

	protected function getAction() {
		return 'close-open-topic';
	}

	public function getAllowedParams() {
		return array(
			'moderationState' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => array( AbstractRevision::MODERATED_CLOSED, 'restore' ),
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
		return 'Close or open a Flow topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=close-open-topic&cotmoderationState=close&cotsummary=Ahhhh&cotprev_revision=xjs&workflow=',
		);
	}
}
