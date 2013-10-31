<?php

namespace Flow;

use Flow\Data\MultiDimArray;

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
	 * @param string $type
	 * @param string[optional] $option Function can be overloaded in case the
	 * desired value is nested deeper
	 * @return mixed|null Requested value or null if missing
	 */
	public function getValue( $action, $type /* [, $option = null [, ...]] */ ) {
		try {
			return $this->actions[func_get_args()];
		} catch ( \OutOfBoundsException $e ) {
			// Do nothing; the whole remainder of this method is fail-case.
		}

		/*
		 * If no value is found, check if the action is not actually referencing
		 * another action (for BC reasons), then try fetching the requested data
		 * from that action.
		 */
		try {
			$action = $this->actions[$action];
			if ( is_string( $action ) ) {
				// Replace action name in arguments.
				$arguments = func_get_args();
				array_shift( $arguments );
				array_unshift( $arguments, $action );

				return call_user_func_array( array( $this, 'getValue' ), $arguments );
			}
		} catch ( \OutOfBoundsException $e ) {
			// Do nothing; the whole remainder of this method is fail-case.
		}

		return null;
	}
}
