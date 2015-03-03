<?php

class ApiFlowUndoEditPost extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'undo-edit-post', 'uep' );
	}

	protected function getAction() {
		return 'undo-edit-post';
	}

	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	public function getAllowedParams() {
		return array(
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'startId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'endId' => array(
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
			'postId' => 'The id of the post that is being edited',
			'startId' => 'The revision id to start the undo at',
			'endId' => 'The revision id to end the undo at',
			'prev_revision' => 'Revision id of the current post revision to check for edit conflicts',
			'content' => 'Content for post',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return '';
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
			'action=flow&submodule=undo-edit-post&page=Topic:S2tycnas4hcucw8w&uaepostId=???&uaestartId=???&uaeendId=???&ueprevId=???&uepprev_revision=???&uepcontent=Nice%20to&20meet%20you'
				=> 'apihelp-flow+undo-edit-post-example-1',
		);
	}
}
