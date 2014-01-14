<?php

class ApiFlowEditPost extends ApiFlowBase {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-post', 'ep' );
	}

	protected function getAction() {
		return 'edit-post';
	}

	protected function getBlockName() {
		return 'topic';
	}

	public function getAllowedParams() {
		return array(
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'postId' => 'Post ID',
			'content' => 'Content for post',
		);
	}

	public function getDescription() {
		return 'Edits a post\'s content';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-post&eppostId=???&epcontent=Nice%20to&20meet%20you&workflow=',
		);
	}

}