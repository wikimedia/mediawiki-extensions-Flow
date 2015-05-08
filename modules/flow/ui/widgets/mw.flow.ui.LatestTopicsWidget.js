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

	this.messages = {
		newest: mw.msg( 'flow-newest-topics' ),
		updated: mw.msg( 'flow-recent-topics' )
	};

	this.button = new OO.ui.ButtonWidget( {
		framed: false,
		indicator: 'down',
		label: this.messages.newest,
		classes: [ 'flow-ui-latestTopicsWidget-button' ]
	} );
	this.reorderMenuOptionWidget = new OO.ui.MenuOptionWidget( {
		icon: 'clock',
		label: this.messages.updated
	} );
	this.reorderMenu = new OO.ui.MenuSelectWidget( {
		classes: [ 'flow-ui-latestTopicsWidget-menu' ],
		items: [ this.reorderMenuOptionWidget ]
	} );
	this.reorderMenu.connect( this, { choose: 'onReorderMenuChoose' } );

	this.toggleReorderType( 'newest' );

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
	this.system.resetBoard( this.getReorderType() );
	this.toggleReorderType();
};

/**
 * Get reorder type - 'newest' or 'updated'
 *
 * @return {string} Reorder type
 */
mw.flow.ui.LatestTopicsWidget.prototype.getReorderType = function () {
	return this.reorderType;
};

/**
 * Toggle reorder type between 'newest' and 'updated'
 *
 * @param {string} [type] Reorder type
 */
mw.flow.ui.LatestTopicsWidget.prototype.toggleReorderType = function ( type ) {
	this.reorderType = type || { newest: 'updated', updated: 'newest' }[ this.reorderType ];

	// Change button label
	this.button.setLabel( this.messages[this.reorderType] );
	// Change reorder menu option label
	this.reorderMenuOptionWidget.setLabel( this.messages[ { newest: 'updated', updated: 'newest' }[ this.reorderType ] ] )
};
