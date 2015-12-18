<?php

namespace Flow\Api;

use ApiBase;
use Flow\Conversion\Utils;
use Flow\Exception\WikitextException;

class ApiParsoidUtilsFlow extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();
		$page = $this->getTitleOrPageId( $params );

		try {
			$content = Utils::convert( $params['from'], $params['to'], $params['content'], $page->getTitle() );
		} catch ( WikitextException $e ) {
			$code = $e->getErrorCode();
			$this->dieUsage( $this->msg( $code )->inContentLanguage()->useDatabase( false )->plain(), $code,
				$e->getStatusCode(), array( 'detail' => $e->getMessage() ) );
			return; // helps static analysis know execution does not continue past self::dieUsage
		}

		$result = array(
			'format' => $params['to'],
			'content' => $content,
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

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			"action=flow-parsoid-utils&from=wikitext&to=html&content='''lorem'''+''blah''&title=Main_Page"
				=> 'apihelp-flow-parsoid-utils-example-1',
		);
	}
}
