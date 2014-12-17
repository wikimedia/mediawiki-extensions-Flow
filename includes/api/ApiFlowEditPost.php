<?php

class ApiFlowEditPost extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-post', 'ep' );
	}

	protected function getAction() {
		return 'edit-post';
	}

	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
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
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'postId' => 'Post ID',
			'prev_revision' => 'Revision id of the current post revision to check for edit conflicts',
			'content' => 'Content for post',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Edits a post\'s content';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=edit-post&page=Topic:S2tycnas4hcucw8w&eppostId=???&epprev_revision=???&epcontent=Nice%20to&20meet%20you',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-post&page=Topic:S2tycnas4hcucw8w&eppostId=???&epprev_revision=???&epcontent=Nice%20to&20meet%20you'
				=> 'apihelp-flow+edit-post-example-1',
		);
	}
}
