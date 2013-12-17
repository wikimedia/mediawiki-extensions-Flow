<?php

use Flow\ParsoidUtils;

class ApiParsoidUtilsFlow extends ApiBase {
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'parse' );
	}

	public function execute() {
		$params = $this->extractRequestParams();
		$page = $this->getTitleOrPageId( $params );

		$result = array(
			'format' => $params['to'],
			'content' => ParsoidUtils::convert( $params['from'], $params['to'], $params['content'], $page->getTitle() ),
		);
		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return array(
			'from' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
			),
			'to' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => array( 'html', 'wikitext' ),
			),
			'content' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'title' => null,
			'pageid' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => 'integer'
			),
		);
	}

	public function getParamDescription() {
		$p = $this->getModulePrefix();
		return array(
			'from' => 'Format of content tossed in',
			'to' => 'Format to convert content to',
			'content' => 'Content to be converted',
			'title' => "Title of the page. Cannot be used together with {$p}pageid",
			'pageid' => "ID of the page. Cannot be used together with {$p}title",
		);
	}

	public function getDescription() {
		return 'Convert text from/to wikitext/html using Parsoid';
	}

	public function getExamples() {
		return array(
			"api.php?action=flow-parsoid-utils&parsefrom=wikitext&parseto=html&parsecontent='''lorem'''+''blah''&parsetitle=Main_Page",
		);
	}
}
