<?php

class ApiFlowViewTopicSummary extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vts' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'topicsummary' );
	}

	protected function getAction() {
		return 'topic-summary-view';
	}

	public function getAllowedParams() {
		return array(
			'contentFormat' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'contentFormat' => 'Format to return the content in (html|wikitext)',
		);
	}

	public function getDescription() {
		return 'View a topic summary';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=topic-summary-view&vtscontentFormat=wikitext&workflow=',
		);
	}
}
