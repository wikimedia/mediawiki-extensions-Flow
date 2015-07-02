( function ( $ ) {
	/**
	 * Flow search widget
	 *
	 * @class
	 * @extends OO.ui.TextInputWidget
	 * @mixin OO.ui.mixin.LookupElement
	 *
	 * @constructor
	 * @param {Object} [config]
	 * @cfg {mw.Title} [pageTitle] Page title
	 */
	mw.flow.ui.Overlay = function VeUiOverlay( config ) {
		// Parent constructor
		OO.ui.Element.call( this, config );

		// Initialization
		this.$element.addClass( 'flow-ui-overlay' );
	};

	/* Inheritance */

	OO.inheritClass( mw.flow.ui.Overlay, OO.ui.Element );
}( jQuery ) );
