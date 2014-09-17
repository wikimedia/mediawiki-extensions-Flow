<?php

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
		return array( 'topiclist' => $this->extractRequestParams() );
	}

	protected function getAction() {
		return 'view-topiclist';
	}

	public function getAllowedParams() {
		global $wgFlowDefaultLimit;

		return array(
			'offset-dir' => array(
				ApiBase::PARAM_TYPE => array( 'fwd', 'rev' ),
				ApiBase::PARAM_DFLT => 'fwd',
			),
			'sortby' => array(
				ApiBase::PARAM_TYPE => array( 'newest', 'updated' ),
				ApiBase::PARAM_DFLT => 'newest',
			),
			'savesortby' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
			'offset-id' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),
			'offset' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
			),
			'limit' => array(
				// @todo once we have a better idea of the performance of this
				// adjust these to sane defaults
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_DFLT => $wgFlowDefaultLimit,
				ApiBase::PARAM_MAX => $wgFlowDefaultLimit,
				ApiBase::PARAM_MAX2 => $wgFlowDefaultLimit,
			),
			// @todo: I assume render parameter will soon be removed, after
			// frontend rewrite
			'render' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'offset-dir' => 'Direction to get topics for',
			'sortby' => 'Sorting option of the topics',
			'savesortby' => 'Save sortby option, if set',
			'offset-id' => 'Offset value (in UUID format) to start fetching topics at',
			'offset' => 'Offset value to start fetching topics at',
			'limit' => 'Amount of topics to fetch',
			'render' => 'Renders (in HTML) the topics, if set',
		);
	}

	public function getDescription() {
		return 'View a list of topics';
	}

	public function getExamples() {
		return array(
			'api.php?action=flow&submodule=view-topiclist&workflow=',
		);
	}
}
