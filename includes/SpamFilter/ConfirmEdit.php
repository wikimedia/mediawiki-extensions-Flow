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

		// first check if the submitted content is offensive (as flagged by
		// ConfirmEdit), next check for a (valid) captcha to have been entered
		if ( $captcha->shouldCheck( $editPage, $newContent, false, false ) && !$captcha->passCaptcha() ) {
			// getting here means we submitted bad content without good captcha
			// result (or any captcha result at all) - let's get the captcha
			// HTML to display as error message!
			$html = $captcha->getForm();
			$msg = wfMessage( 'flow-spam-confirmedit' )->rawParams( $html );
			return Status::newFatal( $msg );
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
