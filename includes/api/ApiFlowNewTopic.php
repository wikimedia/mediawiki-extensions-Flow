<?php

class ApiFlowNewTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'nt' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topiclist' => $this->extractRequestParams() );
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
				ApiBase::PARAM_REQUIRED => true,
			),
			'omitreplies' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'topic' => 'Text for new topic header',
			'content' => 'Content for new topic',
			'omitreplies' => 'Whether to omit replies (posts); if true, only each topic itself will be included',
		);
	}

	public function getDescription() {
		return 'Creates a new Flow topic on the given workflow';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=new-topic&page=Talk:Sandbox&nttopic=Hi&ntcontent=Nice%20to&20meet%20you',
		);
	}
}
