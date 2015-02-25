<?php

namespace Flow\Import\LiquidThreadsApi;

use ArrayIterator;
use Flow\Import\IImportHeader;
use Flow\Import\IImportObject;
use Flow\Import\IImportPost;
use Flow\Import\IImportSummary;
use Flow\Import\IImportTopic;
use Flow\Import\ImportException;
use Flow\Import\IObjectRevision;
use Flow\Import\IRevisionableObject;
use Iterator;
use MWTimestamp;
use Title;
use User;

class ImportRevision implements IObjectRevision {
	/** @var IImportObject **/
	protected $parentObject;

	/** @var array **/
	protected $apiResponse;

	/**
	 * Creates an ImportRevision based on a MW page revision
	 *
	 * @param array         $apiResponse  An element from api.query.revisions
	 * @param IImportObject $parentObject
	 */
	function __construct( array $apiResponse, IImportObject $parentObject ) {
		$this->apiResponse = $apiResponse;
		$this->parent = $parentObject;
	}

	/**
	 * @return string
	 */
	public function getText() {
		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$contentKey = isset( $this->apiResponse[ApiResult::META_CONTENT] )
				? $this->apiResponse[ApiResult::META_CONTENT]
				: '*';
		} else {
			$contentKey = '*';
		}

		return $this->apiResponse[$contentKey];
	}

	public function getTimestamp() {
		return wfTimestamp( TS_MW, $this->apiResponse['timestamp'] );
	}

	public function getAuthor() {
		return $this->apiResponse['user'];
	}

	public function getObjectKey() {
		return $this->parent->getObjectKey() . ':rev:' . $this->apiResponse['revid'];
	}
}
