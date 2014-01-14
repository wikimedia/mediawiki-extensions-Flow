<?php

use Flow\WorkflowLoader;
use Flow\Model\UUID;
use Flow\Container;
use Flow\TalkpageManager;
use Flow\Block\AbstractBlock;


abstract class ApiFlowBase extends ApiBase {

	/**
	 * Use $this->getContainer() to access this
	 * @var Container $container
	 */
	private $container;

	/** @var WorkflowLoader $loader */
	protected $loader;

	/** @var TalkpageManager $controller */
	protected $controller;

	/** @var Title|bool $page */
	public $page;

	public function __construct( $api, $modName, $prefix = '' ) {
		parent::__construct( $api->getMain(), $modName, $prefix );
	}

	/*
	 * Return the name of the flow action
	 * @return string
	 */
	protected function getAction() {
		return ApiFlow::getActionFromClass( get_class( $this ) );
	}


	/**
	 *
	 * @return null|UUID
	 */
	protected function getId() {
		$params = $this->extractRequestParams();
		if ( isset( $params['workflow'] ) ) {
			return UUID::create( $params['workflow'] );
		}

		return null;
	}

	/**
	 * @return Container
	 */
	protected function getContainer() {
		if ( is_null( $this->container ) ) {
			$this->container = Container::getContainer();
		}

		return $this->container;
	}

	/**
	 * @return WorkflowLoader
	 */
	protected function getLoader() {
		if ( $this->loader === null ) {
			$container = $this->getContainer();
			$this->loader = $container['factory.loader.workflow']
				->createWorkflowLoader( $this->page, $this->getId() );
		}

		return $this->loader;
	}

	/**
	 * @return TalkpageManager
	 */
	protected function getOccupationController() {
		if ( $this->controller === null ) {
			$container = $this->getContainer();
			$this->controller = $container['occupation_controller'];
		}

		return $this->controller;
	}

	/**
	 * @return string
	 */
	abstract protected function getBlockName();

	/**
	 * @return DerivativeRequest
	 */
	protected function getModifiedRequest() {
		$extracted = $this->extractRequestParams();
		// @fixme this is terrible, but we'll fix the interface later
		$params = array( $this->getBlockName() => $extracted );

		return new DerivativeRequest(
			$this->getRequest(),
			$params,
			$this->getRequest()->wasPosted() // Note that we also enforce this at the API level.
		);
	}

	public function execute() {
		$loader = $this->getLoader();
		$blocks = $loader->createBlocks();
		/** @var \Flow\Model\Workflow $workflow */
		$workflow = $loader->getWorkflow();
		$action = $this->getAction();
		$controller = $this->getOccupationController();
		$user = $this->getUser();
		$params = $this->extractRequestParams();

		$isNew = $workflow->isNew();
		$article = new Article( $workflow->getArticleTitle(), 0 );

		// @fixme: this is a hack; see ParsoidUtils::convert
		global $wgFlowParsoidTitle;
		$wgFlowParsoidTitle = $workflow->getArticleTitle();

		$isNew = $workflow->isNew();
		// Is this making unnecesary db round trips?
		if ( !$isNew ) {
			$controller->ensureFlowRevision( $article );
		}

		/** @var AbstractBlock $block */
		foreach ( $blocks as $block ) {
			$block->init( $action, $user );
		}

		$blocksToCommit = $loader->handleSubmit( $action, $blocks, $user, $this->getModifiedRequest() );
		if ( $blocksToCommit ) {
			$commitResults = $loader->commit( $workflow, $blocksToCommit );
			$savedBlocks = array( '_element' => 'block' );

			foreach( $blocksToCommit as $block ) {
				$savedBlocks[] = $block->getName();
			}

			$output[$action] = array(
				'result' => array(),
			);

			$doRender = ( $params['render'] != false );

			foreach( $commitResults as $key => $value ) {
				$output[$action]['result'][$key] = $this->processCommitResult( $value, $doRender );
			}
			if ( $isNew && !$workflow->isNew() ) {
				// Workflow was just created, ensure its underlying page is owned by flow
				$controller->ensureFlowRevision( $article );
			}
		} else {
			$output[$action] = array(
				'result' => 'error',
				'errors' => array(),
			);

			foreach( $blocks as $block ) {
				if ( $block->hasErrors() ) {
					$errors = $block->getErrors();
					$nativeErrors = array();

					foreach( $errors as $key ) {
						$nativeErrors[$key]['message'] = $block->getErrorMessage( $key )->plain();
						$nativeErrors[$key]['extra'] = $block->getErrorExtra( $key );
					}

					$output[$action]['errors'][$block->getName()] = $nativeErrors;
				}
			}
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	protected function processCommitResult( $result, $render = true ) {
		$container = $this->getContainer();
		$templating = $container['templating'];
		$output = array();
		foreach( $result as $key => $value ) {
			if ( $value instanceof UUID ) {
				$output[$key] = $value->getHex();
			} elseif ( $key === 'render-function' ) {
				if ( $render ) {
					$function = $value;
					$output['rendered'] = $function( $templating );
				}
			} else {
				$output[$key] = $value;
			}
		}

		return $output;
	}

	public function mustBePosted() {
		return true;
	}

}