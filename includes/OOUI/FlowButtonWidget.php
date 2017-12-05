<?php

namespace Flow\OOUI;

/**
 * Generic widget for buttons.
 */
class FlowButtonWidget extends \OOUI\ButtonWidget {
	protected $name;

	public function __construct( array $config = [ 'infusable' => true ] ) {

		parent::__construct( $config );

		$definition = [];
		if ( isset( $config['definition' ] ) && $config['definition' ] ) {
			$definition = $config['definition'];
		}
		$this->widgetData = json_encode( $definition );

		if ( isset( $config['addOnClick' ] ) && $config['addOnClick' ] ) {
			$this->button->setAttributes( [
				'onClick' => $this->getOnClickDeferredAction( $name, $action ),
			] );
		}

		$this->button->setAttributes( [
			'data-widget' => $this->widgetData,
		] );
	}

	protected function getOnClickDeferredAction( $definition ) {
		return 'window.mwSDInitActions = window.mwSDInitActions || [];' .
			'this.className+=" flow-ui-pending";' .
			'window.mwSDInitActions.push( [this, ' . $definition . ']);' .
			'console.log("onClick intercepted",window.mwSDInitActions);' .
			'return false;';
	}
}
