<?php

namespace Flow;

use Flow\Data\Utils\MultiDimArray;

class FlowActions {
	/**
	 * @var MultiDimArray
	 */
	protected $actions = array();

	/**
	 * @param array $actions
	 */
	public function __construct( array $actions ) {
		$this->actions = new MultiDimArray();
		$this->actions[] = $actions;
	}

	/**
	 * @return array
	 */
	public function getActions() {
		return array_keys( $this->actions->all() );
	}

	/**
	 * @param string $action
	 * @param string[optional] $type
	 * @param string[optional] $option Function can be overloaded in case the
	 * desired value is nested deeper
	 * @return mixed|null Requested value or null if missing
	 */
	public function getValue( $action, $type = null /* [, $option = null [, ...]] */ ) {
		$arguments = func_get_args();

		try {
			return $this->actions[$arguments];
		} catch ( \OutOfBoundsException $e ) {
			// Do nothing; the whole remainder of this method is fail-case.
		}

		/*
		 * If no value is found, check if the action is not actually referencing
		 * another action (for BC reasons), then try fetching the requested data
		 * from that action.
		 */
		try {
			$referencedAction = $this->actions[$action];
			if ( is_string( $referencedAction ) && $referencedAction != $action ) {
				// Replace action name in arguments.
				array_shift( $arguments );
				array_unshift( $arguments, $referencedAction );

				return call_user_func_array( array( $this, 'getValue' ), $arguments );
			}
		} catch ( \OutOfBoundsException $e ) {
			// Do nothing; the whole remainder of this method is fail-case.
		}

		return null;
	}
}
