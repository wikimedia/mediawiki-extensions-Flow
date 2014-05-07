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
		return 'View a board header';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=header-view&vhcontentFormat=wikitext&workflow=',
		);
	}
}
