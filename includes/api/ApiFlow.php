<?php

use \Flow\Container;
use Flow\Model\UUID;

class ApiFlow extends ApiBase {

	/** @var ApiModuleManager $moduleManager */
	private $moduleManager;

	private static $modules = array(
		// POST
		'new-topic' => 'ApiFlowNewTopic',
		'edit-header' => 'ApiFlowEditHeader',
		'edit-post' => 'ApiFlowEditPost',
		'edit-topic-summary' => 'ApiFlowEditTopicSummary',
		'reply' => 'ApiFlowReply',
		'moderate-post' => 'ApiFlowModeratePost',
		'moderate-topic' => 'ApiFlowModerateTopic',
		'edit-title' => 'ApiFlowEditTitle',
		'close-open-topic' => 'ApiFlowCloseOpenTopic',

		// GET
		'header-view' => 'ApiFlowViewHeader',
		'topic-summary-view' => 'ApiFlowViewTopicSummary',
	);

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
		$this->moduleManager = new ApiModuleManager( $this );
		$this->moduleManager->addModules( self::$modules, 'submodule' );
	}

	public function getModuleManager() {
		return $this->moduleManager;
	}

	public function execute() {
		$params = $this->extractRequestParams();
		/** @var $module ApiFlowBase */
		$module = $this->moduleManager->getModule( $params['submodule'], 'submodule' );

		// The checks for POST and tokens are the same as ApiMain.php
		$wasPosted = $this->getRequest()->wasPosted();
		if ( !$wasPosted && $module->mustBePosted() ) {
			$this->dieUsageMsg( array( 'mustbeposted', $params['submodule'] ) );
		}
		$salt = $module->getTokenSalt();
		if ( $salt !== false ) {
			if ( !isset( $params['token'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'token' ) );
			}

			if ( !$this->getUser()->matchEditToken(
				$params['token'],
				$salt,
				$this->getRequest() )
			) {
				$this->dieUsageMsg( 'sessionfailure' );
			}
		}

		$module->extractRequestParams();
		$module->profileIn();
		$module->setWorkflowParams( $this->getPage( $params ), $this->getId( $params ) );
		$module->doRender( $params['render'] );
		$module->execute();
		wfRunHooks( 'APIFlowAfterExecute', array( $module ) );
		$module->profileOut();
	}

	/**
	 * @param array $params
	 * @return null|UUID
	 */
	protected function getId( $params ) {
		if ( isset( $params['workflow'] ) ) {
			try {
				return UUID::create( $params['workflow'] );
			} catch ( \Flow\Exception\InvalidInputException $e ) {
				$this->dieUsage( 'An invalid value was passed as the workflow id', 'invalid-workflow' );
			}
		}

		return null;
	}

	/**
	 * @param array $params
	 * @return Title|bool false if no title provided
	 */
	protected function getPage( $params ) {
		if ( isset( $params['page'] ) ) {
			$page = Title::newFromText( $params['page'] );
			if ( !$page ) {
				$this->dieUsage( 'Invalid page provided', 'invalid-page' );
			}
			/** @var Flow\TalkpageManager $controller */
			$controller = Container::get( 'occupation_controller' );
			if ( !$controller->isTalkpageOccupied( $page ) ) {
				$this->dieUsage( 'Page provided does not have Flow enabled', 'invalid-page' );
			}
		} else {
			$page = false;
		}

		return $page;
	}

	/**
	 * @todo figure out if this is still needed
	 */
	protected function doRerender( $blocks ) {
		$templating = $this->container['templating'];

		$output = array();

		$output['count'] = count( $blocks );

		foreach( $blocks as $block ) {
			$output[$block->getName()] = $block->render( $templating, array() );
		}

		return $output;
	}

	public function getAllowedParams() {
		return array(
			'submodule' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $this->moduleManager->getNames( 'submodule' ),
			),
			'workflow' => null,
			'page' => null,
			'token' => '',
			'render' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getDescription() {
		return 'Allows actions to be taken on Flow pages.';
	}

	public function getParamDescription() {
		return array(
			'submodule' => 'The Flow submodule to invoke',
			'workflow' => 'The Workflow to take the action on',
			'page' => 'The page to take the action on',
			'token' => 'An edit token',
			'render' => 'Set this to something to include a block-specific rendering in the output',
		);
	}

	/**
	 * Override the parent to generate help messages for all available query modules.
	 * @return string
	 */
	public function makeHelpMsg() {

		// Use parent to make default message for the query module
		$msg = parent::makeHelpMsg();

		$querySeparator = str_repeat( '--- ', 12 );
		$moduleSeparator = str_repeat( '*** ', 14 );
		$msg .= "\n$querySeparator Flow: Submodules  $querySeparator\n\n";
		$msg .= $this->makeHelpMsgHelper( 'submodule' );
		$msg .= "\n\n$moduleSeparator Modules: continuation  $moduleSeparator\n\n";

		return $msg;
	}

	/**
	 * For all modules of a given group, generate help messages and join them together
	 * @param string $group Module group
	 * @return string
	 */
	private function makeHelpMsgHelper( $group ) {
		$moduleDescriptions = array();

		$moduleNames = $this->moduleManager->getNames( $group );
		sort( $moduleNames );
		foreach ( $moduleNames as $name ) {
			/**
			 * @var $module ApiFlowBase
			 */
			$module = $this->moduleManager->getModule( $name );

			$msg = ApiMain::makeHelpMsgHeader( $module, $group );
			$msg2 = $module->makeHelpMsg();
			if ( $msg2 !== false ) {
				$msg .= $msg2;
			}
			$moduleDescriptions[] = $msg;
		}

		return implode( "\n", $moduleDescriptions );
	}

	public function getHelpUrls() {
		return array(
			'https://www.mediawiki.org/wiki/Extension:Flow/API',
		);
	}

	public function getExamples() {
		 return array(
			 'api.php?action=flow&submodule=edit-header&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you&workflow=',
		 );
	}

	public function mustBePosted() {
		return false;
	}

	public function needsToken() {
		return false;
	}

	public function getTokenSalt() {
		return false;
	}
}
