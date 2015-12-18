<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowReply extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'rep' );
	}

	/**
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
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
			'format' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 'wikitext',
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=reply&page=Topic:S2tycnas4hcucw8w&repreplyTo=050e554490c2b269143b080027630f57&repcontent=Nice%20to&20meet%20you&repformat=wikitext'
				=> 'apihelp-flow+reply-example-1',
		);
	}
}
