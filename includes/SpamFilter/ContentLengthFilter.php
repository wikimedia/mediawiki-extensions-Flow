<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use IContextSource;
use Status;
use Title;

class ContentLengthFilter implements SpamFilter {

	/**
	 * @var integer The maximum number of characters of wikitext to allow through filter
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
	 * @return Status
	 */
	public function validate( IContextSource $context, AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		return $newRevision->getContentLength() > $this->maxLength
			? Status::newFatal( 'flow-error-content-too-long', $this->maxLength )
			: Status::newGood();
	}
}
