<?php

use Flow\Model\UUID;

class ApiQueryRevisionContentFlow extends ApiQueryBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'rev' );
	}

	public function execute() {
		$params = $this->extractRequestParams();

		$container = include __DIR__ . "/../../container.php";
		if ( !isset( $container[$params['container']] ) ) {
			throw new \MWException( 'Unknown container: '. $params['container'] );
		}

		$post = $container[$params['container']]->find( array( 'rev_id' => UUID::create( $params['id'] ) ) );
		if ( !isset( $post[0] ) ) {
			throw new \MWException( 'Unknown post: '. $params['id'] );
		}

		$result = array(
			'format' => 'html',
			'content' => $post[0]->getContent(),
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
		);
	}

	public function getParamDescription() {
		return array(
			'id' => 'Hex-encoded rev_id',
			'container' => 'Revision storage container',
		);
	}

	public function getDescription() {
		return 'Gets parsed content for Revision objects';
	}

	public function getExamples() {
		return array(
			'api.php?action=query&list=flow-revision-content&revid=05021b0a3015fc3eb5dbb8f6b11bc701&revcontainer=storage.post',
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Flow_Portal';
	}

	public function getVersion() {
		return __CLASS__ . '-0.1';
	}
}
