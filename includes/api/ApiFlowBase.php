<?php

use Flow\WorkflowLoader;
use Flow\Model\UUID;
use Flow\Container;
use Flow\TalkpageManager;
use Flow\Model\AbstractRevision;

abstract class ApiFlowBase extends ApiBase {

	/** @var WorkflowLoader $loader */
	protected $loader;

	/** @var Title|bool $page */
	protected $page;

	/** @var UUID|null $id */
	protected $id;

	/** @var bool $render */
	protected $render;

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
	 * Allows the main ApiFlow instance to set default parameters
	 *
	 * @param Title|bool $page
	 * @param UUID|null $workflow
	 */
	public function setWorkflowParams( $page, $workflow ) {
		$this->page = $page;
		$this->id = $workflow;
	}

	public function doRender( $do = null ) {
		return wfSetVar( $this->render, $do );
	}

	/*
	 * Return the name of the flow action
	 * @return string
	 */
	abstract protected function getAction();

	/**
	 * @return Container
	 */
	protected function getContainer() {
		return Container::getContainer();
	}

	/**
	 * @return WorkflowLoader
	 */
	protected function getLoader() {
		if ( $this->loader === null ) {
			$container = $this->getContainer();
			$this->loader = $container['factory.loader.workflow']
				->createWorkflowLoader( $this->page, $this->id );
		}

		return $this->loader;
	}

	/**
	 * @return TalkpageManager
	 */
	protected function getOccupationController() {
		$container = $this->getContainer();
		return $container['occupation_controller'];
	}

	/**
	 * The block names for an actions.  An action may invoke multiple blocks
	 * @return array
	 */
	abstract protected function getBlockNames();

	/**
	 * @return DerivativeRequest
	 */
	protected function getModifiedRequest() {
		$extracted = $this->extractRequestParams();
		// @fixme this is terrible, but we'll fix the interface later
		$params = array();
		foreach ( $this->getBlockNames() as $block ) {
			$params[$block] = $extracted;
		}

		return new DerivativeRequest(
			$this->getRequest(),
			$params,
			$this->getRequest()->wasPosted() // Note that we also enforce this at the API level.
		);
	}

	/**
	 * @param bool $addAliases
	 * @return array
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
	 * @param array $blocks
	 */
	protected function processError( $blocks ) {
		foreach( $blocks as $block ) {
			if ( $block->hasErrors() ) {
				$errors = $block->getErrors();

				foreach( $errors as $key ) {
					$this->getResult()->dieUsage(
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

	public function getHelpUrls() {
		return array(
			'https://www.mediawiki.org/wiki/Extension:Flow/API#' . $this->getAction(),
		);
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getTokenSalt() {
		return '';
	}
}
