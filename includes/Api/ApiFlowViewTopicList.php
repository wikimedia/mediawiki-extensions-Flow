<?php

namespace Flow\Api;

use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

class ApiFlowViewTopicList extends ApiFlowBaseGet {
	public function __construct( $api, $modName ) {
		parent::__construct( $api, $modName, 'vtl' );
	}

	/**
	 * Taken from ext.flow.base.js
	 *
	 * @return array
	 */
	protected function getBlockParams() {
		return [ 'topiclist' => $this->extractRequestParams() ];
	}

	protected function getAction() {
		return 'view-topiclist';
	}

	public function getAllowedParams() {
		global $wgFlowDefaultLimit, $wgFlowMaxLimit;

		return [
			'offset-dir' => [
				ParamValidator::PARAM_TYPE => [ 'fwd', 'rev' ],
				ParamValidator::PARAM_DEFAULT => 'fwd',
			],
			'sortby' => [
				ParamValidator::PARAM_TYPE => [ 'newest', 'updated', 'user' ],
				ParamValidator::PARAM_DEFAULT => 'user',
			],
			'savesortby' => [
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false,
			],
			'offset-id' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'offset' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => false,
			],
			'include-offset' => [
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false,
			],
			'limit' => [
				ParamValidator::PARAM_TYPE => 'limit',
				ParamValidator::PARAM_DEFAULT => $wgFlowDefaultLimit,
				IntegerDef::PARAM_MAX => $wgFlowMaxLimit,
				IntegerDef::PARAM_MAX2 => $wgFlowMaxLimit,
			],
			'toconly' => [
				ParamValidator::PARAM_TYPE => 'boolean',
				ParamValidator::PARAM_DEFAULT => false,
			],
			'format' => [
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext', 'fixed-html' ],
				ParamValidator::PARAM_DEFAULT => 'fixed-html',
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=flow&submodule=view-topiclist&page=Talk:Sandbox'
				=> 'apihelp-flow+view-topiclist-example-1',
		];
	}
}
