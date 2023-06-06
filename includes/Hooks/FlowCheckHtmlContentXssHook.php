<?php

namespace Flow\Hooks;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "FlowCheckHtmlContentXss" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface FlowCheckHtmlContentXssHook {
	/**
	 * Called when output flow content, implement XSS check to prevented display of revision here.
	 *
	 * @param string $rawContent
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onFlowCheckHtmlContentXss( string $rawContent );
}
