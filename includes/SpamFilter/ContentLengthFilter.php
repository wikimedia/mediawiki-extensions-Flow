<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use IContextSource;
use Status;
use Title;

class ContentLengthFilter implements SpamFilter {

	/**
	 * @var int The maximum number of characters of wikitext to allow through filter
	 */
	protected $maxLength;

	public function __construct( $maxLength ) {
		$this->maxLength = $maxLength;
	}

	public function enabled() {
		return true;
	}

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
	) {
		return $newRevision->getContentLength() > $this->maxLength
			? Status::newFatal( 'flow-error-content-too-long', $this->maxLength )
			: Status::newGood();
	}
}
