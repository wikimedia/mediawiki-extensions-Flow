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
	 * @return array
	 */
	protected function getModerationStates() {
		return array(
			AbstractRevision::MODERATED_DELETED,
			AbstractRevision::MODERATED_HIDDEN,
			AbstractRevision::MODERATED_SUPPRESSED,
			// aliases for AbstractRevision::MODERATED_NONE
			'restore', 'unhide', 'undelete', 'unsuppress',
		);
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

	public function getParent() {
		return $this->apiFlow;
	}
}
