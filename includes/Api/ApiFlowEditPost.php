<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;

class ApiFlowEditPost extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-post', 'ep' );
	}

	protected function getAction() {
		return 'edit-post';
	}

	protected function getBlockParams() {
		return [ 'topic' => $this->extractRequestParams() ];
	}

	public function getAllowedParams() {
		return [
			'postId' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'prev_revision' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'content' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'format' => [
				ParamValidator::PARAM_DEFAULT => 'wikitext',
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext' ],
			],
		] + parent::getAllowedParams();
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=edit-post&page=Topic:S2tycnas4hcucw8w&eppostId=???&epprev_revision=???' .
				'&epcontent=Nice%20to&20meet%20you&epformat=wikitext'
				=> 'apihelp-flow+edit-post-example-1',
		];
	}
}
