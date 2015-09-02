/**
 * Container for content that should appear in front of everything else.
 *
 * @class
 * @extends OO.ui.Element
 *
 * @constructor
 * @param {Object} [config] Configuration options
 */
mw.flow.ui.Overlay = function MwFlowUiOverlay( config ) {
	// Parent constructor
	OO.ui.Element.call( this, config );

	// Initialization
	this.$element.addClass( 'flow-ui-overlay' );
};

/* Inheritance */

OO.inheritClass( mw.flow.ui.Overlay, OO.ui.Element );

mw.flow.ui.windowOverlay = new mw.flow.ui.Overlay();
