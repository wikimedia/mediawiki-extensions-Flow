<?php

use \Flow\Container;

class ApiQueryPropFlowInfo extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'fi' );
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

	public function getDescription() {
		return 'Get basic Flow information about a page including whether Flow is enabled on them';
	}

	public function getExamples() {
		return array(
			'api.php?action=query&prop=flowinfo&titles=Talk:Sandbox|Main_Page|Talk:Flow',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Extension:Flow/API#action.3Dquery.26prop.3Dflowinfo';
	}

}
