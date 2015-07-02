<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowViewHeader extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'header' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-header';
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
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'format' => 'Format to return the content in',
			'revId' => 'load a specific revision if provided, otherwise, load the most recent',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'View a board header';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view-header&page=Talk:Sandbox&vhformat=wikitext&revId=',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=view-header&page=Talk:Sandbox&vhformat=wikitext&revId='
				=> 'apihelp-flow+view-header-example-1',
		);
	}
}
