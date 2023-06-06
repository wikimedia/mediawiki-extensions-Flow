<?php

namespace Flow\Hooks;

use Config;
use MessageLocalizer;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "FlowTermsOfUseMessages" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 */
interface FlowTermsOfUseMessagesHook {
	/**
	 * Allows other extensions to change the terms-of-use messages.
	 *
	 * @param array[] &$messages array, map from internal name to array of parameters for MessageLocalizer::msg()
	 * @param MessageLocalizer $context
	 * @param Config $config
	 * @return bool|void True or no return value to continue or false to abort
	 */
	public function onFlowTermsOfUseMessages( array &$messages, MessageLocalizer $context, Config $config );
}
