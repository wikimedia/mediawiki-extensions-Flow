<?php

namespace Flow\Api;

use MediaWiki\Api\ApiQueryBase;
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
		foreach ( $pageSet->getGoodPages() as $pageId => $page ) {
			$pageInfo = [ 'flow' => [] ];
			if ( Title::newFromPageIdentity( $page )->hasContentModel( CONTENT_MODEL_FLOW_BOARD ) ) {
				$pageInfo['flow']['enabled'] = '';
			}
			$this->addPageSubItems( $pageId, $pageInfo );
		}
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
