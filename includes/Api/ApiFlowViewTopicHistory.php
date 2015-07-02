<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowViewTopicHistory extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vth' );
	}

	/**
	 * Taken from ext.flow.base.js
	 *
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-topic-history';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'format' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext', 'fixed-html' ),
				ApiBase::PARAM_DFLT => $wgFlowContentFormat,
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'format' => 'Format to return the content in',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'View the revision history of a topic';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view-topic-history&page=Topic:S2tycnas4hcucw8w&vthformat=wikitext',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=view-topic-history&page=Topic:S2tycnas4hcucw8w&vthformat=wikitext'
				=> 'apihelp-flow+view-topic-history-example-1',
		);
	}
}
