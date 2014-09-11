<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use Status;
use Title;

class ContentLengthFilter implements SpamFilter {

	public function enabled() {
		return true;
	}

	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title = null ) {
		return strlen( $newRevision->getContentRaw() ) > 25600
			? Status::newFatal( 'flow-error-content-too-long', '25600' )
			: Status::newGood();
	}
}
