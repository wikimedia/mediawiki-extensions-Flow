<?php

class ApiFlowReply extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'rep' );
	}

	/**
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'topic' );
	}

	protected function getAction() {
		return 'reply';
	}

	public function getAllowedParams() {
		return array(
			'replyTo' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'replyTo' => 'Post ID to reply to',
			'content' => 'Content for new topic',
		);
	}

	public function getDescription() {
		return 'Replies to a post';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=reply&repreplyTo=050e554490c2b269143b080027630f57&repntcontent=Nice%20to&20meet%20you&workflow=',
		);
	}
}
