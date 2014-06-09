<?php

namespace Flow\SpamFilter;

use Article;
use ConfirmEditHooks;
use EditPage;
use Flow\Model\AbstractRevision;
use RequestContext;
use SimpleCaptcha;
use Status;
use Title;

class ConfirmEdit implements SpamFilter {
	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		$newContent = $newRevision->getContent( 'wikitext' );

		/** @var SimpleCaptcha $captcha */
		$captcha = ConfirmEditHooks::getInstance();
		$editPage = new EditPage( Article::newFromTitle( $title, RequestContext::getMain() ) );

		if ( $captcha->shouldCheck( $editPage, $newContent, false, false ) ) {
			return Status::newFatal( 'flow-spam-confirmedit' ); // @todo: create msg
		}

		return Status::newGood();
	}

	/**
	 * Checks if ConfirmEdit is installed.
	 *
	 * @return bool
	 */
	public function enabled() {
		return class_exists( 'ConfirmEditHooks' );
	}
}
