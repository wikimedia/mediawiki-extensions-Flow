<?php

class ApiFlowViewTopic extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vt' );
	}

	/**
	 * Taken from ext.flow.base.js
	 *
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'topic' );
	}

	protected function getAction() {
		return 'topic-view';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'no-children' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
			'contentFormat' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
				ApiBase::PARAM_DFLT => $wgFlowContentFormat,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'no-children' => 'If set, this won\'t render replies to the requested topic',
			'contentFormat' => 'Format to return the content in',
		);
	}

	public function getDescription() {
		return 'View a topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=topic-view&workflow=',
		);
	}
}
