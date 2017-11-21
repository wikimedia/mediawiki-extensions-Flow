<?php

namespace Flow\OOUI;

/**
 * Generic widget for buttons.
 */
class FlowButtonWidget extends \OOUI\ButtonWidget {
	protected $name;

	public function __construct( array $config = [] ) {
		parent::__construct( array_merge( [ 'infusable' => true ], $config ) );

		if ( isset( $config['addOnClick' ] ) && $config['addOnClick' ] ) {
			$this->button->setAttributes( [
				'onClick' => $this->getOnClickDeferredAction( $name ),
			] );
		}
	}

	protected function getOnClickDeferredAction( $name ) {
		return 'window.mwSDInitActions = window.mwSDInitActions || [];' .
			'window.mwSDInitActions.push( [this, "click"]);' .
			'console.log("clicked",window.mwSDInitActions);' .
			'return false;';
	}
}