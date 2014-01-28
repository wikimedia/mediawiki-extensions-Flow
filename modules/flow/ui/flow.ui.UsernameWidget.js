/**
 * Username widget.
 *
 * Displays a username with relevant controls for accessing related resources.
 *
 * @param {Object} config Configuration options
 */
flow.ui.UsernameWidget = function FlowUiUsernameWidget( config ) {
	// Parent constructor
	OO.ui.Widget.call( this, config );

	// Initialization
	this.$element.addClass( 'flow-ui-usernameWidget' );
};

/* Inheritance */

OO.inheritClass( flow.ui.UsernameWidget, OO.ui.Widget );

/* Methods */
