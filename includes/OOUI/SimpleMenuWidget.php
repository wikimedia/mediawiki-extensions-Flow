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

				$button = new \OOUI\ButtonWidget( array_merge(
					[
						'framed' => false,
						'classes' => $itemClasses,
					],
					$itemData
				) );
				$button->setAttributes( [
					'data-name' => !empty( $itemData[ 'name' ] ) ? $itemData[ 'name' ] : strtolower( str_replace( ' ', '_', $itemData[ 'label' ] ) )
					'onClick' => $this->getOnClickDeferredAction( 'click' ),
				] );

				$items[] = $button;

				$sepClass = '';
			}

			// Add items
			$this->addItems( $items );
		}
	}
}