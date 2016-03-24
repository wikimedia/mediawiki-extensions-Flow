<?php

namespace Flow\Api;

use ApiBase;
use Flow\Block\Block;
use Flow\Container;
use Flow\Model\AbstractRevision;
use Flow\WorkflowLoader;
use Flow\WorkflowLoaderFactory;
use Title;

abstract class ApiFlowBase extends ApiBase {

	/** @var WorkflowLoader $loader */
	protected $loader;

	/** @var Title $page */
	protected $page;

	/** @var ApiFlow $apiFlow */
	protected $apiFlow;

	/**
	 * @param ApiFlow $api
	 * @param string $modName
	 * @param string $prefix
	 */
	public function __construct( $api, $modName, $prefix = '' ) {
		$this->apiFlow = $api;
		parent::__construct( $api->getMain(), $modName, $prefix );
	}

	/**
	 * @return array
	 */
	abstract protected function getBlockParams();

	/**
	 * Returns true if the submodule required the page parameter to be set.
	 * Most submodules will need to be passed the page the API request is for.
	 * For some (e.g. search), this is not needed at all.
	 *
	 * @return bool
	 */
	public function needsPage() {
		return true;
	}

	/**
	 * @param Title $page
	 */
	public function setPage( Title $page ) {
		$this->page = $page;
	}

	/*
	 * Return the name of the flow action
	 * @return string
	 */
	abstract protected function getAction();

	/**
	 * @return WorkflowLoader
	 */
	protected function getLoader() {
		if ( $this->loader === null ) {
 			/** @var WorkflowLoaderFactory $factory */
			$factory = Container::get( 'factory.loader.workflow' );
			$this->loader = $factory->createWorkflowLoader( $this->page );
		}

		return $this->loader;
	}

	/**
	 * @param bool $addAliases
	 * @return string[]
	 */
	protected function getModerationStates( $addAliases = true ) {
		$states = array(
			AbstractRevision::MODERATED_NONE,
			AbstractRevision::MODERATED_DELETED,
			AbstractRevision::MODERATED_HIDDEN,
			AbstractRevision::MODERATED_SUPPRESSED,
		);

		if ( $addAliases ) {
			// aliases for AbstractRevision::MODERATED_NONE
			$states = array_merge( $states, array(
				'restore', 'unhide', 'undelete', 'unsuppress',
			) );
		}

		return $states;
	}

	/**
	 * Kill the request if errors were encountered.
	 * Only the first error will be output:
	 * * dieUsage only outputs one error - we could add more as $extraData, but
	 *   that would mean we'd have to check for flow-specific errors differently
	 * * most of our code just quits on the first error that's encountered, so
	 *   outputting all encountered errors might still not cover everything
	 *   that's wrong with the request
	 *
	 * @param Block[] $blocks
	 */
	protected function processError( $blocks ) {
		foreach( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				// API localization is not implemented fully yet.
				// See https://www.mediawiki.org/wiki/API:Localisation#Errors_and_warnings and T37074
				// We probably really want to use brief messages with ->text() (like dieUsageMsg).
				//
				// But we can't use ->text() with all these messages (though I changed
				// the Flow messages to support this).  Ones provided by core or extensions are often
				// long-form and use wikitext.
				//
				// The standard mechanism to deal with that is dieUsageMsg, but that is English-only,
				// so we can't use it until that's solved.  That means we have to use the long-form HTML
				// rendering, and clients need to support that.
				//
				// Also, it would be nice to use dieBlocked to provide detailed block information, but
				// that is also English-only.
				foreach( $errors as $key ) {
					$this->dieUsage(
						$block->getErrorMessage( $key )->parse(),
						$key,
						200,
						// additional info for this message (e.g. to be used to
						// enable recovery from error, like returning the most
						// recent revision ID to re-submit content in the case
						// of edit conflict)
						array( $key => $block->getErrorExtra( $key ) )
					);
				}
			}
		}
	}

	/**
	 * Override prefix on CSRF token so the same code can be reused for
	 * all modules.  This is a *TEMPORARY* hack please remove as soon as
	 * unprefixed tokens are working correctly again (bug 70099).
	 *
	 * @param string $paramName
	 * @return string
	 */
	public function encodeParamName( $paramName ) {
		return $paramName === 'token'
			? $paramName
			: parent::encodeParamName( $paramName );
	}

	/**
	 * {@inheritDoc}
	 */
	public function getHelpUrls() {
		return array(
			'https://www.mediawiki.org/wiki/Extension:Flow/API#' . $this->getAction(),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function needsToken() {
		return 'csrf';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTokenSalt() {
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getParent() {
		return $this->apiFlow;
	}

}
