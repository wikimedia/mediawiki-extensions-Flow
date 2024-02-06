<?php

namespace Flow\Exception;

/**
 * Category: permission related exception
 */
class PermissionException extends FlowException {
	protected function getErrorCodeList() {
		// flow-error-insufficient-permission
		return [ 'insufficient-permission' ];
	}
}
