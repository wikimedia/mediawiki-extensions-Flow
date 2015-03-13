<?php

namespace Flow\Api;

use ApiBase;
use ApiMain;
use ApiModuleManager;
use Flow\Container;
use Title;

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
		'lock-topic' => 'ApiFlowLockTopic',
		'close-open-topic' => 'ApiFlowLockTopic', // BC: has been renamed to lock-topic

		// GET
		// action 'view' exists in Topic.php & TopicList.php, for topic, post &
		// topiclist - we'll want to know topic-/post- or topiclist-view ;)
		'view-topiclist' => 'ApiFlowViewTopicList',
		'view-post' => 'ApiFlowViewPost',
		'view-topic' => 'ApiFlowViewTopic',
		'view-header' => 'ApiFlowViewHeader',
		'view-topic-summary' => 'ApiFlowViewTopicSummary',
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
		// To avoid API warning, register the parameter used to bust browser cache
		$this->getMain()->getVal( '_' );

		$params = $this->extractRequestParams();
		/** @var $module ApiFlowBase */
		$module = $this->moduleManager->getModule( $params['submodule'], 'submodule' );

		// The checks for POST and tokens are the same as ApiMain.php
		$wasPosted = $this->getRequest()->wasPosted();
		if ( !$wasPosted && $module->mustBePosted() ) {
			$this->dieUsageMsg( array( 'mustbeposted', $params['submodule'] ) );
		}

		if ( $module->needsToken() ) {
			if ( !isset( $params['token'] ) ) {
				$this->dieUsageMsg( array( 'missingparam', 'token' ) );
			}

			if ( is_callable( array( $module, 'validateToken' ) ) ) {
				if ( !$module->validateToken( $params['token'], $params ) ) {
					$this->dieUsageMsg( 'sessionfailure' );
				}
			} else {
				if ( !$this->getUser()->matchEditToken(
					$params['token'],
					$module->getTokenSalt(),
					$this->getRequest() )
				) {
					$this->dieUsageMsg( 'sessionfailure' );
				}
			}
		}

		$module->extractRequestParams();
		$module->profileIn();
		$module->setPage( $this->getPage( $params ) );
		$module->doRender( $params['render'] );
		$module->execute();
		wfRunHooks( 'APIFlowAfterExecute', array( $module ) );
		$module->profileOut();
	}

	/**
	 * @param array $params
	 * @return Title
	 */
	protected function getPage( $params ) {
		$page = Title::newFromText( $params['page'] );
		if ( !$page ) {
			$this->dieUsage( 'Invalid page provided', 'invalid-page' );
		}
		/** @var \Flow\TalkpageManager $controller */
		$controller = Container::get( 'occupation_controller' );
		if ( !$controller->isTalkpageOccupied( $page ) ) {
			// just check for permissions, nothing else to do. if the commit
			// is successfull the OccupationListener will see the new revision
			// and put the flow board in place.
			if ( !$controller->isCreationAllowed( $page, $this->getUser() ) ) {
				$this->dieUsage( 'Page provided does not have Flow enabled', 'invalid-page' );
			}
		}

		return $page;
	}

	public function getAllowedParams() {
		$mainParams = $this->getMain()->getAllowedParams();
		if ( $mainParams['action'][ApiBase::PARAM_TYPE] === 'submodule' ) {
			$submodulesType = 'submodule';
		} else {
			/** @todo Remove this case once support for older MediaWiki is dropped */
			$submodulesType = $this->moduleManager->getNames( 'submodule' );
		}

		return array(
			'submodule' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $submodulesType,
			),
			'page' => array(
				ApiBase::PARAM_REQUIRED => true
			),
			'token' => '',
			'render' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Allows actions to be taken on Flow pages.';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		return array(
			'submodule' => 'The Flow submodule to invoke',
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

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		 return array(
			 'api.php?action=flow&submodule=edit-header&page=Talk:Sandbox&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you',
		 );
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=flow&submodule=edit-header&page=Talk:Sandbox&ehprev_revision=???&ehcontent=Nice%20to&20meet%20you'
				=> 'apihelp-flow-example-1',
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
