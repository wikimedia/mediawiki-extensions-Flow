<?php

use Flow\ParsoidUtils;

class ApiParsoidUtilsFlow extends ApiBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'parse' );
	}

	public function execute() {
		$params = $this->extractRequestParams();

		$result = array(
			'format' => $params['to'],
			'content' => ParsoidUtils::convert( $params['from'], $params['to'], $params['content'] ),
		);
		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return array(
			'from' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'to' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'from' => 'Format of content tossed in (html|wikitext)',
			'to' => 'Format to convert content to (html|wikitext)',
			'content' => 'Content to be converted',
		);
	}

	public function getDescription() {
		return 'Convert text from/to wikitext/html';
	}

	public function getExamples() {
		return array(
			"api.php?action=flow-parsoid-utils&parsefrom=wikitext&parseto=html&parsecontent='''lorem'''+''blah''",
		);
	}

	public function getHelpUrls() {
		return 'https://www.mediawiki.org/wiki/Flow_Portal';
	}

	public function getVersion() {
		return __CLASS__ . '-0.1';
	}
}
