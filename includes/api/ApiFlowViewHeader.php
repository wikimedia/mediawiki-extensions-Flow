<?php

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
			'api.php?action=flow&submodule=view-header&vhcontentFormat=wikitext&workflow=&revId=',
		);
	}
}
