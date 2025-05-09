<?php

namespace Flow\SpamFilter;

use Flow\Model\AbstractRevision;
use Flow\Model\HtmlRenderingInformation;
use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\ConfirmEdit\Hooks as ConfirmEditHooks;
use MediaWiki\Extension\ConfirmEdit\SimpleCaptcha\SimpleCaptcha;
use MediaWiki\MediaWikiServices;
use MediaWiki\Registration\ExtensionRegistry;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;

class ConfirmEdit implements SpamFilter {
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
	) {
		$newContent = $newRevision->getContentInWikitext();
		$oldContent = ( $oldRevision !== null ) ? $oldRevision->getContentInWikitext() : '';

		/** @var SimpleCaptcha $captcha */
		$captcha = ConfirmEditHooks::getInstance();
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );

		// first check if the submitted content is offensive (as flagged by
		// ConfirmEdit), next check for a (valid) captcha to have been entered
		if (
			$captcha->shouldCheck( $wikiPage, $newContent, '', $context, $oldContent ) &&
			!$captcha->passCaptchaLimitedFromRequest( $context->getRequest(), $context->getUser() )
		) {
			// getting here means we submitted bad content without good captcha
			// result (or any captcha result at all) - let's get the captcha
			// information (HTML, modules, etc.) to display as error message!
			$captchaInfo = $captcha->getFormInformation();
			$captchaRenderingInfo = HtmlRenderingInformation::fromArray(
				$captchaInfo
			);

			$msg = wfMessage( 'flow-spam-confirmedit-form' )->rawParams( $captchaInfo['html'] );
			$status = Status::newFatal( $msg );
			$status->setResult( false, $captchaRenderingInfo );
			return $status;
		}

		return Status::newGood();
	}

	/**
	 * Checks if ConfirmEdit is installed.
	 *
	 * @return bool
	 */
	public function enabled() {
		return ExtensionRegistry::getInstance()->isLoaded( 'ConfirmEdit' );
	}
}
