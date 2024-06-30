<?php

namespace Flow\Api;

use ApiQueryBase;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Title\Title;

class ApiQueryPropFlowInfo extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'fli' );
	}

	/**
	 * Use action=query&prop=info instead; check for 'contentmodel' 'flow-board'.
	 * @return bool
	 */
	public function isDeprecated() {
		return true;
	}

	public function execute() {
		$pageSet = $this->getPageSet();
		/** @var PageIdentity $title */
		foreach ( $pageSet->getGoodPages() as $pageid => $title ) {
			$pageInfo = $this->getPageInfo( Title::newFromPageIdentity( $title ) );
			$this->addPageSubItems( $pageid, $pageInfo );
		}
	}

	/**
	 * In the future we can add more Flow related info here
	 * @param Title $title
	 * @return array
	 */
	protected function getPageInfo( Title $title ) {
		$result = [ 'flow' => [] ];
		if ( $title->getContentModel() === CONTENT_MODEL_FLOW_BOARD ) {
			$result['flow']['enabled'] = '';
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&prop=flowinfo&titles=Talk:Sandbox|Main_Page|Talk:Flow'
				=> 'apihelp-query+flowinfo-example-1',
		];
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Special:MyLanguage/Extension:Flow/API#action.3Dquery.26prop.3Dflowinfo';
	}

}
