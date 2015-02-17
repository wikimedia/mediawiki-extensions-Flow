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
		) + parent::getAllowedParams();;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'topic' => 'Text for new topic title',
			'content' => 'Content for the topic\'s initial reply',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Creates a new Flow topic on the given workflow';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=new-topic&page=Talk:Sandbox&nttopic=Hi&ntcontent=Nice%20to&20meet%20you',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=new-topic&page=Talk:Sandbox&nttopic=Hi&ntcontent=Nice%20to&20meet%20you'
				=> 'apihelp-flow+new-topic-example-1',
		);
	}
}
