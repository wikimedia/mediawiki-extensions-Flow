/**
 * Flow LatestTopicsWidget
 *
 * @extends OO.ui.Widget
 * @constructor
 * @param {mw.flow.dm.Board} board Board model
 * @param {Object} [config]
 *
 */
mw.flow.ui.ReorderTopicsWidget = function mwFlowUiLatestTopicsWidget( board, config ) {
	config = config || {};

	// Parent constructor
	mw.flow.ui.ReorderTopicsWidget.super.call( this, config );

	this.board = board;

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
		icon: 'beta',
		label: this.messages.updated,
		widget: this.button
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

OO.inheritClass( mw.flow.ui.ReorderTopicsWidget, OO.ui.Widget );

/**
 * Respond to button click
 */
mw.flow.ui.ReorderTopicsWidget.prototype.onButtonClick = function () {
	this.reorderMenu.toggle();
};

mw.flow.ui.ReorderTopicsWidget.prototype.onReorderMenuChoose = function ( item ) {
	this.board.reset( this.getReorderType() );
	this.toggleReorderType();
};

/**
 * Get reorder type - 'newest' or 'updated'
 *
 * @return {string} Reorder type
 */
mw.flow.ui.ReorderTopicsWidget.prototype.getReorderType = function () {
	return this.reorderType;
};

/**
 * Toggle reorder type between 'newest' and 'updated'
 *
 * @param {string} [type] Reorder type
 */
mw.flow.ui.ReorderTopicsWidget.prototype.toggleReorderType = function ( type ) {
	this.reorderType = type || { newest: 'updated', updated: 'newest' }[ this.reorderType ];

	// Change button label
	this.button.setLabel( this.messages[this.reorderType] );

	// Change reorder menu option label
	this.reorderMenuOptionWidget.setLabel(
		this.messages[ { newest: 'updated', updated: 'newest' }[ this.reorderType ] ]
	);

	// Change the icon
	this.reorderMenuOptionWidget.setIcon( type === 'newest' ? 'beta' : 'clock' );
};
