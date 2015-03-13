<?php

namespace Flow\Api;

use ApiBase;

class ApiFlowNewTopic extends ApiFlowBasePost {

	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'nt' );
	}

	/**
	 * Taken from ext.flow.base.js
	 * @return array
	 */
	protected function getBlockParams() {
		return array( 'topiclist' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'new-topic';
	}

	public function getAllowedParams() {
		return array(
			'topic' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'format' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 'wikitext',
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
			),
		) + parent::getAllowedParams();
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'topic' => 'Text for new topic title',
			'content' => 'Content for the topic\'s initial reply',
			'format' => 'Format of the content (wikitext|html)',
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Creates a new Flow topic on the given page, or workflow';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=new-topic&page=Talk:Sandbox&nttopic=Hi&ntcontent=Nice%20to&20meet%20you&ntformat=wikitext',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=new-topic&page=Talk:Sandbox&nttopic=Hi&ntcontent=Nice%20to&20meet%20you&ntformat=wikitext'
				=> 'apihelp-flow+new-topic-example-1',
		);
	}
}
