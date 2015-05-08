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
	// On element-click handlers
	//

	/**
	 *
	 * @param {Event} event
	 */
	function FlowBoardComponentSideRailFeatureMixinToggleCallback( event ) {
		$( '.flow-component' ).toggleClass( 'expanded' );
	}
	FlowBoardComponentSideRailFeatureMixin.UI.events.interactiveHandlers.toggleSideRail = FlowBoardComponentSideRailFeatureMixinToggleCallback;

	//
	// Private functions
	//

	// Mixin to FlowComponent
	mw.flow.mixinComponent( 'component', FlowBoardComponentSideRailFeatureMixin );
}( jQuery, mediaWiki ) );
