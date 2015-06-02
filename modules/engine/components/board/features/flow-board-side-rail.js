/*!
 * Contains Side Rail functionality.
 */

( function ( $, mw ) {
	/**
	 * Binds handlers for side rail in board header.
	 * @param {jQuery} $container
	 * @this FlowComponent
	 * @constructor
	 */
	function FlowBoardComponentSideRailFeatureMixin( $container ) {
		// Bind element handlers
		this.bindNodeHandlers( FlowBoardComponentSideRailFeatureMixin.UI.events );
	}
	OO.initClass( FlowBoardComponentSideRailFeatureMixin );

	FlowBoardComponentSideRailFeatureMixin.UI = {
		events: {
			apiPreHandlers: {},
			apiHandlers: {},
			interactiveHandlers: {},
			loadHandlers: {}
		}
	};

	//
	// Load handlers
	//

	/**
	 *
	 * @param {Event} event
	 */
	function FlowBoardComponentSideRailFeatureMixinLoadCallback( event ) {
		if ( !mw.user.isAnon() ) {
			if ( mw.user.options.get( 'side-rail-collapsed' ) ) {
				$( '.flow-component' ).addClass( 'expanded' );
			}
		}
	}
	FlowBoardComponentSideRailFeatureMixin.UI.events.loadHandlers.loadSideRail = FlowBoardComponentSideRailFeatureMixinLoadCallback;

	//
	// On element-click handlers
	//

	/**
	 *
	 * @param {Event} event
	 */
	function FlowBoardComponentSideRailFeatureMixinToggleCallback( event ) {
		var sideRailCollapsed = $( '.flow-component' ).toggleClass( 'expanded' ).hasClass( 'expanded' );

		if ( !mw.user.isAnon() ) {
			// update the user preferences; no preferences for anons
			new mw.Api().saveOption( 'side-rail-collapsed', sideRailCollapsed );
			// ensure we also see that preference in the current page
			mw.user.options.set( 'side-rail-collapsed', sideRailCollapsed );
		}
	}
	FlowBoardComponentSideRailFeatureMixin.UI.events.interactiveHandlers.toggleSideRail = FlowBoardComponentSideRailFeatureMixinToggleCallback;

	//
	// Private functions
	//

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentSideRailFeatureMixin );
}( jQuery, mediaWiki ) );
