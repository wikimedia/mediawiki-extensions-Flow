<?php

class ApiFlowEditTopicSummary extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-topic-summary', 'ets' );
	}

	protected function getAction() {
		return 'edit-topic-summary';
	}

	protected function getBlockNames() {
		return array( 'topicsummary' );
	}

	public function getAllowedParams() {
		return array(
			'prev_revision' => null,
			'summary' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current topic summary revision to check for edit conflicts. Null for a new topic summary revision',
			'summary' => 'Content for the summary',
		);
	}

	public function getDescription() {
		return 'Edits a topic summary\'s content';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-topic-summary&wetsprev_revision=???&etssummary=Nice%20to&20meet%20you&workflow=???',
		);
	}
}
