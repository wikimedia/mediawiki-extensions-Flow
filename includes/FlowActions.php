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
			return null;
		}
	}
}
