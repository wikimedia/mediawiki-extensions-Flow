<?php

namespace Flow\Api;

use ApiBase;
use ApiMain;
use ApiModuleManager;
use Flow\Container;
use Hooks;
use Title;

class ApiFlow extends ApiBase {

	/** @var ApiModuleManager $moduleManager */
	private $moduleManager;

	private static $alwaysEnabledModules = array(
		// POST
		'new-topic' => 'Flow\Api\ApiFlowNewTopic',
		'edit-header' => 'Flow\Api\ApiFlowEditHeader',
		'edit-post' => 'Flow\Api\ApiFlowEditPost',
		'edit-topic-summary' => 'Flow\Api\ApiFlowEditTopicSummary',
		'reply' => 'Flow\Api\ApiFlowReply',
		'moderate-post' => 'Flow\Api\ApiFlowModeratePost',
		'moderate-topic' => 'Flow\Api\ApiFlowModerateTopic',
		'edit-title' => 'Flow\Api\ApiFlowEditTitle',
		'lock-topic' => 'Flow\Api\ApiFlowLockTopic',
		'close-open-topic' => 'Flow\Api\ApiFlowLockTopic', // BC: has been renamed to lock-topic
		'undo-edit-header' => 'Flow\Api\ApiFlowUndoEditHeader',
		'undo-edit-post' => 'Flow\Api\ApiFlowUndoEditPost',
		'undo-edit-topic-summary' => 'Flow\Api\ApiFlowUndoEditTopicSummary',

		// GET
		// action 'view' exists in Topic.php & TopicList.php, for topic, post &
		// topiclist - we'll want to know topic-/post- or topiclist-view ;)
		'view-topiclist' => 'Flow\Api\ApiFlowViewTopicList',
		'view-post' => 'Flow\Api\ApiFlowViewPost',
		'view-post-history' => 'Flow\Api\ApiFlowViewPostHistory',
		'view-topic' => 'Flow\Api\ApiFlowViewTopic',
		'view-topic-history' => 'Flow\Api\ApiFlowViewTopicHistory',
		'view-header' => 'Flow\Api\ApiFlowViewHeader',
		'view-topic-summary' => 'Flow\Api\ApiFlowViewTopicSummary',
	);

	private static $searchModules = array(
		'search' => 'Flow\Api\ApiFlowSearch',
	);

	public function __construct( $main, $action ) {
		global $wgFlowSearchEnabled;

		parent::__construct( $main, $action );
		$this->moduleManager = new ApiModuleManager( $this );

		$enabledModules = self::$alwaysEnabledModules;
		if ( $wgFlowSearchEnabled ) {
			$enabledModules += self::$searchModules;
		}

		$this->moduleManager->addModules( $enabledModules, 'submodule' );
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
		if ( $module->needsPage() ) {
			$module->setPage( $this->getPage( $params ) );
		}
		$module->execute();
		Hooks::run( 'APIFlowAfterExecute', array( $module ) );
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
		if ( $page->getContentModel() !== CONTENT_MODEL_FLOW_BOARD ) {
			// Just check for permissions, nothing else to do. The Flow board
			// will be put in place right before the rest of the data is stored
			// (in SubmissionHandler::commit), after everything's been validated.
			$status = $controller->safeAllowCreation( $page, $this->getUser() );
			if ( !$status->isGood() ) {
				$this->dieUsage( "Page provided does not have Flow enabled and safeAllowCreation failed with: " . $status->getMessage()->parse(), 'invalid-page' );
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
				ApiBase::PARAM_REQUIRED => true,
				// supply bogus default - not every action may *need* ?page=
				ApiBase::PARAM_DFLT => Title::newFromText( 'Flow-enabled page', NS_TOPIC )->getPrefixedDBkey(),
			),
			'token' => '',
		);
	}

	public function getHelpUrls() {
		return array(
			'https://www.mediawiki.org/wiki/Extension:Flow/API',
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
}
