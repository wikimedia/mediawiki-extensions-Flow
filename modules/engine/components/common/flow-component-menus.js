/*!
 * Contains flow-menu functionality.
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for flow-menu.
	 * @param {jQuery} $container
	 * @this FlowComponent
	 * @constructor
	 */
	function FlowComponentMenusFeatureMixin( $container ) {
		// Bind events to this instance
		this.bindComponentHandlers( FlowComponentMenusFeatureMixin.eventHandlers );

		// Bind element handlers
		this.bindNodeHandlers( FlowComponentMenusFeatureMixin.UI.events );

		// Bind special toggle menu handler
		$container
			.on(
				'click.FlowBoardComponent focusin.FlowBoardComponent focusout.FlowBoardComponent',
				'.flow-menu',
				this.getDispatchCallback( 'toggleHoverMenu' )
			);
	}
	OO.initClass( FlowComponentMenusFeatureMixin );

	FlowComponentMenusFeatureMixin.eventHandlers = {};
	FlowComponentMenusFeatureMixin.UI = {
		events: {
			loadHandlers: {},
			interactiveHandlers: {}
		}
	};

	//
	// Event handler methods
	//

	/**
	 * On click, focus, and blur of hover menu events, decides whether or not to hide or show the expanded menu
	 * @param {Event} event
	 */
	function flowComponentMenusFeatureMixinToggleHoverMenuCallback( event ) {
		var $this = $( event.target ),
			$menu = $this.closest( '.flow-menu' );

		if ( event.type === 'click' ) {
			// If the caret was clicked, toggle focus
			if ( $this.closest( '.flow-menu-js-drop' ).length ) {
				$menu.toggleClass( 'focus' );

				// This trick lets us wait for a blur event from A instead on body, to later hide the menu on outside click
				if ( $menu.hasClass( 'focus' ) ) {
					$menu.find( '.flow-menu-js-drop' ).find( 'a' ).focus();
				}
			} else {
				// Remove the focus from the menu so it can hide after clicking on something
				setTimeout( function () {
					if ( $this.is( ':focus' ) ) {
						$this.blur();
					}
				}, 50 );
			}
		} else if ( event.type === 'focusin' ) {
			// If we are focused on a menu item (eg. tabbed in), open the whole menu
			$menu.addClass( 'focus' );
		} else if ( event.type === 'focusout' && !$menu.find( 'a' ).filter( ':focus' ).length ) {
			// If we lost focus, make sure no other element in this menu has focus, and then hide the menu
			setTimeout( function () {
				if ( !$menu.find( 'a' ).filter( ':focus' ).length ) {
					$menu.removeClass( 'focus' );
				}
			}, 250 );
		}
	}
	FlowComponentMenusFeatureMixin.eventHandlers.toggleHoverMenu = flowComponentMenusFeatureMixinToggleHoverMenuCallback;

	//
	// On element-click handlers
	//

	/**
	 * Allows you to open a flow-menu from a secondary click handler elsewhere.
	 * Uses data-flow-menu-target="< foo .flow-menu"
	 * @param {Event} event
	 */
	function flowComponentMenusFeatureElementMenuToggleCallback( event ) {
		var $this = $( this ),
			flowComponent = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $this ),
			target = $this.data( 'flowMenuTarget' ),
			$target = $.findWithParent( $this, target );

		event.preventDefault();

		if ( !$target || !$target.length ) {
			flowComponent.debug( 'Could not find openFlowMenu target', arguments );
			return;
		}

		$target.find( '.flow-menu-js-drop' ).trigger( 'click' );
	}
	FlowComponentMenusFeatureMixin.UI.events.interactiveHandlers.menuToggle = flowComponentMenusFeatureElementMenuToggleCallback;

	//
	// On element-load handlers
	//

	/**
	 * When a menu appears, check if it's already got the focus class. If so, re-focus it.
	 * @param {jQuery} $menu
	 */
	function flowComponentMenusFeatureElementLoadCallback( $menu ) {
		// For some reason, this menu is visible, but lacks physical focus
		// This happens when you clone an activated flow-menu
		if ( $menu.hasClass( 'focus' ) && !$menu.find( 'a' ).filter( ':focus' ).length ) {
			// Give it focus again
			$menu.find( '.flow-menu-js-drop' ).find( 'a' ).focus();
		}
	}
	FlowComponentMenusFeatureMixin.UI.events.loadHandlers.menu = flowComponentMenusFeatureElementLoadCallback;

	//
	// Private functions
	//

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowComponentMenusFeatureMixin );
}( jQuery, mediaWiki ) );
