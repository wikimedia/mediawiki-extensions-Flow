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
	protected function getBlockParams() {
		return array( 'topic' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-post';
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
		);
	}

	public function getParamDescription() {
		return array(
			'postId' => 'Id of the post to view',
			'contentFormat' => 'Format to return the content in',
		);
	}

	public function getDescription() {
		return 'View a post';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view-post&page=Topic:S2tycnas4hcucw8w&vppostId=???&vpcontentFormat=wikitext',
		);
	}
}
