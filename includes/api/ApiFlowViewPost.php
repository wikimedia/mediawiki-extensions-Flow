<?php

class ApiFlowViewPost extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vp' );
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
		return 'view';
	}

	public function getAllowedParams() {
		global $wgFlowContentFormat;

		return array(
			'postId' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'contentFormat' => array(
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
				ApiBase::PARAM_DFLT => $wgFlowContentFormat,
			),
			'no-children' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'postId' => 'Id of the post to view',
			'contentFormat' => 'Format to return the content in',
			'no-children' => 'If set, this won\'t render replies to the requested post',
		);
	}

	public function getDescription() {
		return 'View a post';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view&vppostId=???&vpcontentFormat=wikitext&workflow=',
		);
	}
}
