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

// The Moved* series of topics handle the LQT move stubs.  They need to
// have their revision content rewriten from #REDIRECT to a template that
// has visible output like lqt generated per-request.
class MovedImportTopic extends ImportTopic {
	public function getReplies() {
		$topPost = new MovedImportPost( $this->importSource, $this->apiResponse );
		return new ArrayIterator( array( $topPost ) );
	}
}
