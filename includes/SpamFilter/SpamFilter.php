<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use IContextSource;
use Status;
use Title;

interface SpamFilter {
	/**
	 * @param IContextSource $context
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @param Title $ownerTitle
	 * @return Status
	 * @suppress PhanParamReqAfterOpt Nullable, not optional
	 */
	public function validate(
		IContextSource $context,
		AbstractRevision $newRevision,
		AbstractRevision $oldRevision = null,
		Title $title,
		Title $ownerTitle
	);

	/**
	 * @return bool
	 */
	public function enabled();
}
