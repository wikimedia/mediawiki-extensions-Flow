<?php

namespace Flow\Hooks;

use MediaWiki\Output\OutputPage;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "FlowAddModules" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface FlowAddModulesHook {
	/**
	 * Allows other extensions to add relevant modules.
	 *
	 * @param OutputPage $out
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onFlowAddModules( OutputPage $out );
}
