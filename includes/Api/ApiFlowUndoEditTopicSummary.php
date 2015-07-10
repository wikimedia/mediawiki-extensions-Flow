<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowUndoEditTopicSummary extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'uets' );
	}

	protected function getAction() {
		return 'undo-edit-topic-summary';
	}

	protected function getBlockParams() {
		return array(
			'topicsummary' => $this->extractRequestParams(),
			'topic' => array(),
		);
	}

	public function getAllowedParams() {
		return array(
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
			'action=flow&submodule=undo-edit-topic-summary&page=Topic:S2tycnas4hcucw8w&uetsstartId=???&uetsendId=???'
				=> 'apihelp-flow+undo-edit-topic-summary-example-1',
		);
	}
}
