<?php

namespace Flow\OOUI;

/**
 * Generic widget for buttons.
 */
class FlowButtonWidget extends \OOUI\ButtonWidget {
	protected $name;

	public function __construct( array $config = [] ) {

		parent::__construct( $config );

		$action = '';
		if ( isset( $config['action' ] ) && $config['action' ] ) {
			$action = $config['action'];
		}
		if ( isset( $config['addOnClick' ] ) && $config['addOnClick' ] ) {
			$this->button->setAttributes( [
				'onClick' => $this->getOnClickDeferredAction( $name, $action ),
			] );
		}

		$this->widgetData = isset( $config['data'] ) ? $config['data'] : '';
		if ( is_array( $this->widgetData ) ) {
			$this->widgetData = json_encode( $this->widgetData );
		}
		$this->button->setAttributes( [
			'data-widget' => $this->widgetData,
		] );
	}

	protected function getOnClickDeferredAction( $name, $action ) {
		$action = json_encode( $action );
		return 'window.mwSDInitActions = window.mwSDInitActions || [];' .
			'window.mwSDInitActions.push( [this, ' . $action . ']);' .
			'console.log("clicked",window.mwSDInitActions);' .
			'return false;';
	}
}