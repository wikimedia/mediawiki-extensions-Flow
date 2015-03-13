<?php

namespace Flow\Api;

use ApiBase;

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
		) + parent::getAllowedParams();
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=undo-edit-post&page=Topic:S2tycnas4hcucw8w&uaepostId=???&uaestartId=???&uaeendId=???'
				=> 'apihelp-flow+undo-edit-post-example-1',
		);
	}
}
