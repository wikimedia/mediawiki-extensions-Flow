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

class ImportSummary extends PageRevisionedObject implements IImportSummary {
	/** @var ImportSource **/
	protected $source;

	/**
	 * @param array        $apiResponse
	 * @param ImportSource $source
	 * @throws ImportException
	 */
	public function __construct( array $apiResponse, ImportSource $source ) {
		parent::__construct( $source, $apiResponse['pageid'] );
	}

	public function getObjectKey() {
		return $this->importSource->getObjectKey( 'summary_id', $this->pageId );
	}
}
