<?php

class ApiFlowViewHeader extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vh' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockNames() {
		return array( 'header' );
	}

	protected function getAction() {
		return 'header-view';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'contentFormat' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
				ApiBase::PARAM_DFLT => $wgFlowContentFormat,
			),
			'revId' => null,
		);
	}

	public function getParamDescription() {
		return array(
			'contentFormat' => 'Format to return the content in',
			'revId' => 'load a specific revision if provided, otherwise, load the most recent',
		);
	}

	public function getDescription() {
		return 'View a board header';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=header-view&vhcontentFormat=wikitext&workflow=&revId=',
		);
	}
}
