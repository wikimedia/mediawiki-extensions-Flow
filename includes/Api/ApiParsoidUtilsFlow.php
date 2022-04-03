<?php

namespace Flow\Api;

use ApiBase;
use Flow\Conversion\Utils;
use Flow\Exception\WikitextException;
use Wikimedia\ParamValidator\ParamValidator;

class ApiParsoidUtilsFlow extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();
		$page = $this->getTitleOrPageId( $params );

		try {
			$content = Utils::convert(
				$params['from'],
				$params['to'],
				$params['content'],
				$page->getTitle()
			);
		} catch ( WikitextException $e ) {
			$code = $e->getErrorCode();
			$this->dieWithError( $code, $code,
				[ 'detail' => $e->getMessage() ], $e->getStatusCode()
			);
		}

		$result = [
			'format' => $params['to'],
			'content' => $content,
		];
		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}

	public function getAllowedParams() {
		return [
			'from' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext' ],
			],
			'to' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => [ 'html', 'wikitext' ],
			],
			'content' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'title' => null,
			'pageid' => [
				ParamValidator::PARAM_ISMULTI => false,
				ParamValidator::PARAM_TYPE => 'integer'
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			"action=flow-parsoid-utils&from=wikitext&to=html&content='''lorem'''+''blah''&title=Main_Page"
				=> 'apihelp-flow-parsoid-utils-example-1',
		];
	}
}
