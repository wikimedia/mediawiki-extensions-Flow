<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowViewTopicHistory extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vth' );
	}

	/**
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'topic' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'view-topic-history';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return [
			'format' => [
				ApiBase::PARAM_TYPE => [ 'html', 'wikitext', 'fixed-html' ],
				// never default to unfixed html, only serve that when specifically asked!
				ApiBase::PARAM_DFLT => $wgFlowContentFormat === 'html' ? 'fixed-html' : $wgFlowContentFormat,
			],
		];
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=view-topic-history&page=Topic:S2tycnas4hcucw8w&vthformat=wikitext'
				=> 'apihelp-flow+view-topic-history-example-1',
		];
	}
}
