<?php

use Flow\Model\UUID;
use Flow\Container;

class ApiQueryRevisionContentFlow extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'rev' );
	}

	public function execute() {
		global $wgUser;

		$params = $this->extractRequestParams();

		$container = Container::getContainer();

		$post = $container['storage']->getStorage( $params['container'] )->find( array( 'rev_id' => UUID::create( $params['id'] ) ) );
		if ( !$post ) {
			throw new \MWException( 'Unknown post: '. $params['id'] );
		}
		$post = array_shift( $post );

		$result = array(
			'format' => $params['format'],
			'content' => $post->getContent( $wgUser, $params['format'] ),
		);
		$this->getResult()->addValue( 'query', $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return array(
			'id' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'container' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'format' => array(
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => 'html',
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
			),
		);
	}

	public function getParamDescription() {
		return array(
			'id' => 'Hex-encoded rev_id',
			'container' => 'Revision storage container',
			'format' => 'Content format: html or wikitext',
		);
	}

	public function getDescription() {
		return 'Gets parsed content for Revision objects';
	}

	public function getExamples() {
		return array(
			'api.php?action=query&list=flow-revision-content&revid=05021b0a3015fc3eb5dbb8f6b11bc701&revcontainer=PostRevision',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Flow_Portal';
	}

	public function getVersion() {
		return __CLASS__ . '-0.1';
	}
}
