<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowModeratePost extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'mp' );
	}

	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'moderate-post';
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
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'moderationState' => 'What level to moderate at',
			'reason' => 'Reason for moderation',
			'postId' => 'Id of post to moderate',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Moderates a Flow post';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=moderate-post&page=Topic:S2tycnas4hcucw8w&mppostId=050f30e34c87beebcd54080027630f57&mpmoderationState=delete&mpreason=Ahhhh',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=moderate-post&page=Topic:S2tycnas4hcucw8w&mppostId=050f30e34c87beebcd54080027630f57&mpmoderationState=delete&mpreason=Ahhhh'
				=> 'apihelp-flow+moderate-post-example-1',
		);
	}
}
