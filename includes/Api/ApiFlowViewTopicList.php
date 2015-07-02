<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowViewTopicList extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vtl' );
	}

	/**
	 * Taken from ext.flow.base.js
	 *
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topiclist' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-topiclist';
	}

	public function getAllowedParams() {
		global $wgFlowDefaultLimit, $wgFlowMaxLimit, $wgFlowContentFormat;

		return array(
			'offset-dir' => array(
				ApiBase::PARAM_TYPE => array( 'fwd', 'rev' ),
				ApiBase::PARAM_DFLT => 'fwd',
			),
			'sortby' => array(
				ApiBase::PARAM_TYPE => array( 'newest', 'updated', 'user' ),
				ApiBase::PARAM_DFLT => 'user',
			),
			'savesortby' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
			'offset-id' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),
			'offset' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),
			'include-offset' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
			'limit' => array(
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => $wgFlowDefaultLimit,
				ApiBase::PARAM_MAX => $wgFlowMaxLimit,
				ApiBase::PARAM_MAX2 => $wgFlowMaxLimit,
			),
			'toconly' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
			'format' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext', 'fixed-html' ),
				// never default to unfixed html, only serve that when specifically asked!
				ApiBase::PARAM_DFLT => $wgFlowContentFormat === 'html' ? 'fixed-html' : $wgFlowContentFormat,
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'offset-dir' => 'Direction to get topics for',
			'sortby' => 'Sorting option of the topics',
			'savesortby' => 'Save sortby option, if set',
			'offset-id' => 'Offset value (in UUID format) to start fetching topics at',
			'offset' => 'Offset value to start fetching topics at',
			'include-offset' => 'Includes the offset item in the results as well',
			'limit' => 'Number of topics to fetch',
			'toconly' => 'Whether to respond with only the information required for the TOC',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'View a list of topics';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view-topiclist&page=Talk:Sandbox',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=view-topiclist&page=Talk:Sandbox'
				=> 'apihelp-flow+view-topiclist-example-1',
		);
	}
}
