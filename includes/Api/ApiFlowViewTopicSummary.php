<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowViewTopicSummary extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vts' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topicsummary' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-topic-summary';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'format' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext', 'fixed-html' ),
				// never default to unfixed html, only serve that when specifically asked!
				ApiBase::PARAM_DFLT => $wgFlowContentFormat === 'html' ? 'fixed-html' : $wgFlowContentFormat,
			),
			'revId' => null,
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=view-topic-summary&page=Topic:S2tycnas4hcucw8w&vtsformat=wikitext&revId='
				=> 'apihelp-flow+view-topic-summary-example-1',
		);
	}
}
