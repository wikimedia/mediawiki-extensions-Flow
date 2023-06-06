<?php

namespace Flow\Hooks;

use Config;
use Flow\Api\ApiFlowBase;
use MediaWiki\HookContainer\HookContainer;
use MessageLocalizer;
use OutputPage;

/**
 * This is a hook runner class, see docs/Hooks.md in core.
 * @internal
 */
class HookRunner implements
	APIFlowAfterExecuteHook,
	FlowAddModulesHook,
	FlowCheckHtmlContentXssHook,
	FlowTermsOfUseMessagesHook
{
	private HookContainer $hookContainer;

	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	/**
	 * @inheritDoc
	 */
	public function onAPIFlowAfterExecute( ApiFlowBase $module ) {
		return $this->hookContainer->run(
			'APIFlowAfterExecute',
			[ $module ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function onFlowAddModules( OutputPage $out ) {
		return $this->hookContainer->run(
			'FlowAddModules',
			[ $out ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function onFlowCheckHtmlContentXss( string $rawContent ) {
		return $this->hookContainer->run(
			'FlowCheckHtmlContentXss',
			[ $rawContent ]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function onFlowTermsOfUseMessages( array &$messages, MessageLocalizer $context, Config $config ) {
		return $this->hookContainer->run(
			'FlowTermsOfUseMessages',
			[ &$messages, $context, $config ]
		);
	}
}
