<?php

class ApiFlowNewTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'nt' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'topiclist' );
	}

	protected function getAction() {
		return 'new-topic';
	}

	public function getAllowedParams() {
		return array(
			'topic' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => false,
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
