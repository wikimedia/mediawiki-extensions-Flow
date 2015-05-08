/**
 * Flow LatestTopicsWidget
 *
 * @extends OO.ui.Widget
 * @constructor
 * @param {mw.flow.dm.System} system
 * @param {Object} [config]
 *
 */
mw.flow.ui.LatestTopicsWidget = function mwFlowUiLatestTopicsWidget( system, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.ui.LatestTopicsWidget.super.call( this, config );

	this.system = system;

	this.button = new OO.ui.ButtonWidget( {
		framed: false,
		indicator: 'expand',
		label: mw.msg( 'flow-newest-topics' ),
		classes: [ 'flow-ui-latestTopicsWidget-button' ]
	} );
	this.reorderMenu = new OO.ui.MenuSelectWidget( {
		classes: [ 'flow-ui-latestTopicsWidget-menu' ],
		items: [
			new OO.ui.MenuOptionWidget( {
				icon: 'clock',
				label: mw.msg( 'flow-recent-topics' )
			} )
		]
	} );
	this.reorderMenu.connect( this, { choose: 'onReorderMenuChoose' } );

	// Events
	this.button.connect( this, { click: 'onButtonClick' } );

	// Initialize
	this.$element
		.addClass( 'flow-ui-latestTopicsWidget' )
		.append(
			this.button.$element,
			this.reorderMenu.$element
		);
};

/* Initialization */

OO.inheritClass( mw.flow.ui.LatestTopicsWidget, OO.ui.Widget );

/**
 * Respond to button click
 */
mw.flow.ui.LatestTopicsWidget.prototype.onButtonClick = function () {
	this.reorderMenu.toggle();
};

mw.flow.ui.LatestTopicsWidget.prototype.onReorderMenuChoose = function ( item ) {
	console.log( 'choose', item );
};
