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

			// some captcha implementations need CSS and/or JS, which is added
			// via their getForm() methods (which we just called) -
			// let's extract those and respond them along with the form HTML
			global $wgOut;
			$html = $wgOut->buildCssLinks() .
				$wgOut->getScriptsForBottomQueue( false ) .
				$html;

			$html = $this->safeHtml( $html );

			$msg = wfMessage( 'flow-spam-confirmedit-form' )->rawParams( $html );
			return Status::newFatal( $msg );
		}

		return Status::newGood();
	}

	/**
	 * TL;DR: this will replace document.write calls.
	 *
	 * We've captured CSS & JS stuff as well, to ensure the captcha functions
	 * well. However, that JS part has a document.write() part to launch the
	 * resourceloader scripts.
	 * Because there's no proper spec for document.write, browsers implement
	 * this differently. In FireFox, when the page has already loaded, this
	 * implicitly does a document.open() call, which will (in our JS) then
	 * trigger a new, blank page to be loaded (or rather, we'll get the "are
	 * you sure you want to leave this page" prompt)
	 *
	 * @see http://stackoverflow.com/questions/25398005/why-document-write-behaves-differently-in-firefox-and-chrome
	 *
	 * @param string $html
	 * @return string
	 */
	public function safeHtml( $html ) {
		return str_replace( 'document.write', 'document.body.innerHTML += ', $html );
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
