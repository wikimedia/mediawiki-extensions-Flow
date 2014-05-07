<?php

class ApiFlowEditPost extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-post', 'ep' );
	}

	protected function getAction() {
		return 'edit-post';
	}

	protected function getBlockNames() {
		return array( 'topic' );
	}

	public function getAllowedParams() {
		return array(
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'prev_revision' => array(
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
			'prev_revision' => 'Revision id of the current post revision to check for edit conflicts',
			'content' => 'Content for post',
		);
	}

	public function getDescription() {
		return 'Edits a post\'s content';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-post&eppostId=???&epprev_revision=???&epcontent=Nice%20to&20meet%20you&workflow=',
		);
	}
}
