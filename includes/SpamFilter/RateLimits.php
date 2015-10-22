<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use IContextSource;
use Status;
use Title;

class RateLimits implements SpamFilter {
	/**
	 * @param IContextSource $context
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( IContextSource $context, AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		if ( $context->getUser()->pingLimiter( 'edit' ) ) {
			return Status::newFatal( 'actionthrottledtext' );
		}

		return Status::newGood();
	}

	/**
	 * Checks if SpamRegex is enabled.
	 *
	 * @return bool
	 */
	public function enabled() {
		return true;
	}
}
