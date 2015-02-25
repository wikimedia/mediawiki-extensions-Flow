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

class MovedImportPost extends ImportPost {
	public function getRevisions() {
		$factory = function( $data, $parent ) {
			return new MovedImportRevision( $data, $parent );
		};
		$pageData = $this->importSource->getPageData( $this->pageId );
		return new RevisionIterator( $pageData, $this, $factory );
	}
}
