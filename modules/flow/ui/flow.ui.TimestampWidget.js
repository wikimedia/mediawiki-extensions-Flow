/**
 * Timestamp widget.
 *
 * Displays a timestamp in a user friendly way.
 *
 * @param {Object} config Configuration options
 */
flow.ui.TimestampWidget = function FlowUiTimestampWidget( config ) {
	// Parent constructor
	OO.ui.Widget.call( this, config );

	// Initialization
	this.$element.addClass( 'flow-ui-timestampWidget' );
};

/* Inheritance */

OO.inheritClass( flow.ui.TimestampWidget, OO.ui.Widget );

/* Methods */
