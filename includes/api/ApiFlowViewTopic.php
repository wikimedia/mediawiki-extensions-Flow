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
		return array( 'topiclist' );
	}

	protected function getAction() {
		return 'view'; // @todo: see comment in ApiFlow - make another action for this?
	}

	public function getAllowedParams() {
		return array(
			'contentFormat' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'no-children' => array(
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'contentFormat' => 'Format to return the content in (html|wikitext)',
			'no-children' => 'If set, this won\'t render replies to the requested post',
		);
	}

	public function getDescription() {
		return 'View a topic';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view&vtcontentFormat=wikitext&workflow=',
		);
	}
}
