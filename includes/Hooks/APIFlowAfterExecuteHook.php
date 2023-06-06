<?php

namespace Flow\Hooks;

use Flow\Api\ApiFlowBase;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "APIFlowAfterExecute" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface APIFlowAfterExecuteHook {
	/**
	 * @param ApiFlowBase $module A sub module of API's action=flow
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onAPIFlowAfterExecute( ApiFlowBase $module );
}
