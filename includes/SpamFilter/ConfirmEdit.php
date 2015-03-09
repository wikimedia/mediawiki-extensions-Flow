<?php

namespace Flow\SpamFilter;

use ConfirmEditHooks;
use Flow\Model\AbstractRevision;
use SimpleCaptcha;
use Status;
use Title;
use WikiPage;

class ConfirmEdit implements SpamFilter {
	/**
	 * @param AbstractRevision $newRevision
	 * @param AbstractRevision|null $oldRevision
	 * @param Title $title
	 * @return Status
	 */
	public function validate( AbstractRevision $newRevision, AbstractRevision $oldRevision = null, Title $title ) {
		global $wgOut;
		$newContent = $newRevision->getContent( 'wikitext' );

		/** @var SimpleCaptcha $captcha */
		$captcha = ConfirmEditHooks::getInstance();
		$wikiPage = new WikiPage( $title );

		// first check if the submitted content is offensive (as flagged by
		// ConfirmEdit), next check for a (valid) captcha to have been entered
		if ( $captcha->shouldCheck( $wikiPage, $newContent, false, $wgOut->getContext(), false ) && !$captcha->passCaptcha() ) {
			// getting here means we submitted bad content without good captcha
			// result (or any captcha result at all) - let's get the captcha
			// HTML to display as error message!
			$html = $captcha->getForm();

			// some captcha implementations need CSS and/or JS, which is added
			// via their getForm() methods (which we just called) -
			// let's extract those and respond them along with the form HTML
			$html = $wgOut->buildCssLinks() .
				$wgOut->getScriptsForBottomQueue( true ) .
				$html;

			$msg = wfMessage( 'flow-spam-confirmedit-form' )->rawParams( $html );
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
