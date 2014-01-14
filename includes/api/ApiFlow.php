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
		'reply' => 'ApiFlowReply',
		'moderate-topic' => 'ApiFlowModerateTopic',
		'moderate-post' => 'ApiFlowModeratePost',
		'hide-post' => 'ApiFlowHidePost',
		'delete-post' => 'ApiFlowDeletePost',
		'suppress-post' => 'ApiFlowSuppressPost',
		'restore-post' => 'ApiFlowRestorePost',
		'edit-title' => 'ApiFlowEditTitle',
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
		$modules = array();
		$this->instantiateModules( $modules, 'submodule' );
		// This is some of the logic from ApiQuery::initModules
		$wasPosted = $this->getRequest()->wasPosted();
		/** @var $module ApiFlowBase */
		foreach ( $modules as $moduleName => $module ) {
			if ( !$wasPosted && $module->mustBePosted() ) {
				$this->dieUsageMsg( array( 'mustbeposted', $moduleName ) );
			}
			$module->profileIn();
			$module->execute();
			wfRunHooks( 'APIFlowAfterExecute', $module );
			$module->profileOut();
		}

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

	protected function doRerender( $blocks ) {
		$templating = $this->container['templating'];

		$output = array();

		$output['count'] = count( $blocks );

		foreach( $blocks as $block ) {
			$output[$block->getName()] = $block->render( $templating, array() );
		}

		return $output;
	}

	/**
	 * Create instances of all modules requested by the client
	 * @fixme Copied from ApiQuery::instantiateModules, don't duplicate code
	 * @param array $modules to append instantiated modules to
	 * @param string $param Parameter name to read modules from
	 */
	private function instantiateModules( &$modules, $param ) {
		if ( isset( $this->params[$param] ) ) {
			foreach ( $this->params[$param] as $moduleName ) {
				$instance = $this->modulemanager->getModule( $moduleName, $param );
				if ( $instance === null ) {
					ApiBase::dieDebug( __METHOD__, 'Error instantiating module' );
				}
				// Ignore duplicates. TODO 2.0: die()?
				if ( !array_key_exists( $moduleName, $modules ) ) {
					$modules[$moduleName] = $instance;
				}
			}
		}
	}

	// @fixme need help generation code from ApiQuery


	public function getDescription() {
		 return 'Allows actions to be taken on Flow Workflows';
	}

	public function getAllowedParams() {
		return array(
			'submodule' => array(
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => $this->modulemanager->getNames( 'submodules' ),
			),
			'workflow' => array(
				ApiBase::PARAM_DFLT => null,
			),
			'page' => null,
			'params' => array(
				ApiBase::PARAM_DFLT => '{}',
			),
			'token' => array(
				ApiBase::PARAM_REQUIRED => true,
			),
			'render' => array(
				ApiBase::PARAM_DFLT => false,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'action' => 'The action to take',
			'workflow' => 'The Workflow to take an action on',
			'params' => 'The parameters to pass',
			'token' => 'A token retrieved from api.php?action=tokens&type=flow',
			'render' => 'Set this to something to include a block-specific rendering in the output',
		);
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
