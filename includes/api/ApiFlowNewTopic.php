<?php

class ApiFlowNewTopic extends ApiFlowBase {

	public function __construct( $api ) {
		parent::__construct( $api, 'new-topic', 'nt' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getUsedParameters() {
		// @todo can we just use getAllowedParams?
		return array( 'topic', 'content' );
	}

	public function getAllowedParams() {
		// @todo do we need to array_merge with parent?
		return array(
			'topic' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'topic' => 'Text for new topic header',
			'content' => 'Content for new topic',
		);
	}

	public function getDescription() {
		return 'Creates a new Flow topic on the given workflow';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=new-topic&nttopic=Hi&ntcontent=Nice%20to&20meet%20you&workflow=',
		);
	}

}