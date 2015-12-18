<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowModerateTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'mt' );
	}

	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'moderate-topic';
	}

	public function getAllowedParams() {
		return array(
			'moderationState' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $this->getModerationStates(),
			),
			'reason' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=moderate-topic&page=Topic:S2tycnas4hcucw8w&mtmoderationState=delete&mtreason=Ahhhh'
				=> 'apihelp-flow+moderate-topic-example-1',
		);
	}
}
