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
			'offset-id' => array(),
			'offset' => array(),
			'offset-dir' => array(),
			'sortby' => array(),
			'savesortby' => array(
				// @todo boolean
			),
			'limit' => array(
				// @todo integer default $wgFlowSomethingSomething
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
			'http://localhost:8080/w/api.php?action=flow&format=json&submodule=topiclist-view&page=User_talk:Zomg&vtloffset-id=rvwqmzkngb7k8el6&vtloffset-dir=fwd&vtllimit=10',
		);
	}
}
