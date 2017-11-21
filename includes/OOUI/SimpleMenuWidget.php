<?php

namespace Flow\OOUI;

/**
 * A widget representing a simple post
 */
class SimpleMenuWidget extends BaseUiWidget {
	use \OOUI\GroupElement;

	/**
	 * @param array $config Configuration options
	 * @param Array[] $config['menuItems'] Buttons to add
	 * @param ButtonWidget[] $config['items'] Buttons to add
	 */
	public function __construct( array $config = [] ) {
		// Parent constructor
		parent::__construct( $config );

		$group = new \OOUI\Tag( 'div' );
		$group->addClasses( [ 'mw-flow-ui-simpleMenuWidget-menu' ] );

		// Traits
		$this->initializeGroupElement( array_merge( $config, [ 'group' => $group ] ) );

		if (
			isset( $config['align'] ) &&
			in_array( $config['align'], [ 'backwards', 'forwards' ] )
		) {
			$this->addClasses( [ 'mw-flow-ui-simpleMenuWidget-align-' . $config['align'] ] );
		}

		$trigger = new \OOUI\ButtonWidget( [
			'framed' => false,
			'icon' => 'ellipsis',
			'classes' => [ 'mw-flow-ui-simpleMenuWidget-trigger' ]
		] );

		// Initialization
		$this
			->appendContent( $trigger, $group )
			->addClasses( [ 'mw-flow-ui-simpleMenuWidget' ] );

		// Add menu items
		if ( isset( $config['menuItems'] ) ) {
			$items = [];
			$sepClass = '';

			// Prepare the menu items
			foreach ( $config['menuItems'] as $itemData ) {
				$itemClasses = [ 'mw-flow-ui-simpleMenuWidget-item' ];

				if ( $itemData === 'separator' ) {
					// If the current item is a separator, set the
					// class for the next item and continue
					$sepClass = 'mw-flow-ui-simpleMenuWidget-separator';
					continue;
				}

				if ( $sepClass ) {
					// If the previous item was a separator,
					// add the separator class to this item
					$itemClasses[] = $sepClass;
				}

				// Note: We're using the extended Flow\OOUI\ButtonWidget
				// so we can have access to adding an 'onClick' method
				$items[] = new FlowButtonWidget( array_merge_recursive(
					[
						'framed' => false,
						'classes' => $itemClasses,
						'addOnClick' => true,
						'definition' => [
							'widget' => 'menu',
						],
					],
					$itemData
				) );

				$sepClass = '';
			}

			// Add items
			$this->addItems( $items );
		}
	}
}