<?php

class ApiFlowEditTopicSummary extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-topic-summary', 'uets' );
	}

	protected function getAction() {
		return 'undo-edit-topic-summary';
	}

	protected function getBlockParams() {
		return array(
			'topicsummary' => $this->extractRequestParams(),
			'topic' => array(),
		);
	}

	public function getAllowedParams() {
		return array(
			'startId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'endId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'prev_revision' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
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
			'startId' => 'Revision id to start undo at',
			'endId' => 'Revision id to end undo at',
			'prev_revision' => 'Revision id of the current topic summary revision to check for edit conflicts. Null for a new topic summary revision',
			'summary' => 'Content for the summary',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Undoes an edit to a topic summary\'s content';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array_keys( $this->getExamplesMessages() );
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=undo-edit-topic-summary&page=Topic:S2tycnas4hcucw8w&uetsstartId=???&uetsendId=???&uetsprev_revision=???&uetssummary=Nice%20to&20meet%20you'
				=> 'apihelp-flow+undo-edit-topic-summary-example-1',
		);
	}
}
