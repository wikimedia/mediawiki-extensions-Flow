<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowViewPostHistory extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vph' );
	}

	/**
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-post-history';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'format' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext', 'fixed-html' ),
				// never default to unfixed html, only serve that when specifically asked!
				ApiBase::PARAM_DFLT => $wgFlowContentFormat === 'html' ? 'fixed-html' : $wgFlowContentFormat,
			),
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=view-post-history&page=Topic:S2tycnas4hcucw8w&vphpostId=???&vphformat=wikitext'
				=> 'apihelp-flow+view-post-history-example-1',
		);
	}
}
