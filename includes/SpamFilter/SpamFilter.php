<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use MediaWiki\Context\IContextSource;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

interface SpamFilter {
	/**
	 * @param IContextSource $context
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @param Title $ownerTitle
	 * @return Status
	 */
	public function validate(
		IContextSource $context,
		AbstractRevision $newRevision,
		?AbstractRevision $oldRevision,
		Title $title,
		Title $ownerTitle
	);

	/**
	 * @return bool
	 */
	public function enabled();
}
