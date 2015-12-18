<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowEditTopicSummary extends ApiFlowBasePost {

	public function __construct( $api ) {
		parent::__construct( $api, 'edit-topic-summary', 'ets' );
	}

	protected function getAction() {
		return 'edit-topic-summary';
	}

	protected function getBlockParams() {
		return array(
			'topicsummary' => $this->extractRequestParams(),
			'topic' => array(),
		);
	}

	public function getAllowedParams() {
		return array(
			'prev_revision' => null,
			'summary' => array(
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
			'action=flow&submodule=edit-topic-summary&page=Topic:S2tycnas4hcucw8w&wetsprev_revision=???&etssummary=Nice%20to&20meet%20you&etsformat=wikitext'
				=> 'apihelp-flow+edit-topic-summary-example-1',
		);
	}
}
