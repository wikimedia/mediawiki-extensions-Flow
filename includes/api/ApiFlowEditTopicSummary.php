<?php

class ApiFlowEditTopicSummary extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-topic-summary', 'ets' );
	}

	protected function getAction() {
		return 'edit-topic-summary';
	}

	protected function getBlockParams() {
		return array( 'topicsummary' => $this->extractRequestParams() );
	}

	public function getAllowedParams() {
		return array(
			'prev_revision' => null,
			'summary' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'prev_revision' => 'Revision id of the current topic summary revision to check for edit conflicts. Null for a new topic summary revision',
			'summary' => 'Content for the summary',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Edits a topic summary\'s content';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-topic-summary&page=Topic:S2tycnas4hcucw8w&wetsprev_revision=???&etssummary=Nice%20to&20meet%20you',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-topic-summary&page=Topic:S2tycnas4hcucw8w&wetsprev_revision=???&etssummary=Nice%20to&20meet%20you'
				=> 'apihelp-flow+edit-topic-summary-example-1',
		);
	}
}
