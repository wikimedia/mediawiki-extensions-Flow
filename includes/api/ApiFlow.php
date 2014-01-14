<?php

use Flow\Model\UUID;

class ApiFlow extends ApiBase {

	/** @var Flow\Container $container */
	protected $container;

	/** @var \Flow\WorkflowLoader $loader */
	protected $loader;

	/** @var ApiModuleManager $modulemanager */
	protected $modulemanager;

	private $params;

	static $modules = array(
		'new-topic' => 'ApiFlowNewTopic',
		'edit-header' => 'ApiFlowEditHeader',
		'edit-post' => 'ApiFlowEditPost',
		/*'reply' => 'ApiFlowReply',
		'moderate-topic' => 'ApiFlowModerateTopic',
		'moderate-post' => 'ApiFlowModeratePost',
		'hide-post' => 'ApiFlowHidePost',
		'delete-post' => 'ApiFlowDeletePost',
		'suppress-post' => 'ApiFlowSuppressPost',
		'restore-post' => 'ApiFlowRestorePost',
		'edit-title' => 'ApiFlowEditTitle',*/
	);

	public static function getActionFromClass( $classname ) {
		$flip = array_flip( self::$modules );
		return $flip[$classname];
	}

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
		$this->modulemanager = new ApiModuleManager( $this );
		$this->modulemanager->addModules( self::$modules, 'submodule' );
	}

	public function getModuleManager() {
		return $this->modulemanager;
	}

	public function execute() {
		$this->params = $this->extractRequestParams();
		/** @var $module ApiFlowBase */
		$module = $this->modulemanager->getModule($this->params['submodule'], 'submodule');

		$wasPosted = $this->getRequest()->wasPosted();
		if ( !$wasPosted && $module->mustBePosted() ) {
			$this->dieUsageMsg( array( 'mustbeposted', $this->params['submodule'] ) );
		}

		$module->extractRequestParams();
		$module->profileIn();
		$module->page = $this->getPage( $this->params ); // This feels hackish.
		$module->execute();
		wfRunHooks( 'APIFlowAfterExecute', array( $module ) );
		$module->profileOut();

	}

	protected function processCommitResult( $result, $render = true ) {
		$templating = $this->container['templating'];
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

	public function getDescription() {
		 return 'Allows actions to be taken on Flow Workflows';
	}

	public function getAllowedParams() {
		return array(
			'submodule' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $this->modulemanager->getNames( 'submodule' ),
			),
			'workflow' => null,
			'page' => null,
			'token' => '',
			'render' => array(
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'action' => 'The action to take',
			'workflow' => 'The Workflow to take the action on',
			'page' => 'The page to take the action on',
			'token' => 'A token retrieved from api.php?action=tokens&type=flow',
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
		/*
		$msg .= "\n$querySeparator Query: List  $querySeparator\n\n";
		$msg .= $this->makeHelpMsgHelper( 'list' );
		$msg .= "\n$querySeparator Query: Meta  $querySeparator\n\n";
		$msg .= $this->makeHelpMsgHelper( 'meta' );*/
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

		$moduleNames = $this->modulemanager->getNames( $group );
		sort( $moduleNames );
		foreach ( $moduleNames as $name ) {
			/**
			 * @var $module ApiQueryBase
			 */
			$module = $this->modulemanager->getModule( $name );

			$msg = ApiMain::makeHelpMsgHeader( $module, $group );
			$msg2 = $module->makeHelpMsg();
			if ( $msg2 !== false ) {
				$msg .= $msg2;
			}
			$moduleDescriptions[] = $msg;
		}

		return implode( "\n", $moduleDescriptions );
	}


	public function getExamples() {
		// @todo: fill out an example
		 return array(
			 ''
			 => ''
		 );
	}

	public function mustBePosted() {
		return true;
	}

	public function needsToken() {
		return true;
	}

	public function getTokenSalt() {
		global $wgFlowTokenSalt;
		return $wgFlowTokenSalt;
	}
}
