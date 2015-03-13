<?php

namespace Flow\Api;

use ApiQueryBase;
use Flow\Container;
use Title;

class ApiQueryPropFlowInfo extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'fli' );
	}

	public function execute() {
		$pageSet = $this->getPageSet();
		/** @var Title $title */
		foreach ( $pageSet->getGoodTitles() as $pageid => $title ) {
			$pageInfo = $this->getPageInfo( $title );
			$this->addPageSubItems( $pageid, $pageInfo );
		}
	}

	/**
	 * In the future we can add more Flow related info here
	 * @param Title $title
	 * @return array
	 */
	protected function getPageInfo( Title $title ) {
		/** @var \Flow\TalkpageManager $manager */
		$manager = Container::get( 'occupation_controller' );
		$result = array( 'flow' => array() );
		if ( $manager->isTalkpageOccupied( $title ) ) {
			$result['flow']['enabled'] = '';
		}

		return $result;
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Get basic Flow information about a page including whether Flow is enabled on them';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=query&prop=flowinfo&titles=Talk:Sandbox|Main_Page|Talk:Flow',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=query&prop=flowinfo&titles=Talk:Sandbox|Main_Page|Talk:Flow'
				=> 'apihelp-query+flowinfo-example-1',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:Flow/API#action.3Dquery.26prop.3Dflowinfo';
	}

}
