<?php

class ApiFlowViewTopicList extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vtl' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'topiclist' );
	}

	protected function getAction() {
		return 'view';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'contentFormat' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
				ApiBase::PARAM_DFLT => $wgFlowContentFormat,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'contentFormat' => 'Format to return the content in',
		);
	}

	public function getDescription() {
		return 'View one or more topics';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=topiclist-view&vtlcontentFormat=wikitext&workflow=',
		);
	}
}
