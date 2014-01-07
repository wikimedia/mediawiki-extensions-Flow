<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use Title;
use Status;

interface SpamFilter {
	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision[optional] $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title );

	/**
	 * @return bool
	 */
	public function enabled();
}
