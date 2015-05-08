/**
 * Flow LatestTopicsWidget
 *
 * @extends OO.ui.Widget
 * @constructor
 * @param {mw.flow.dm.Board} board Board model
 * @param {Object} [config]
 * @cfg {string} [defaultSort='newest'] The current default topic sort order
 */
mw.flow.ui.ReorderTopicsWidget = function mwFlowUiReorderTopicsWidget( board, config ) {
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
		label: this.messages.updated
	} );
	this.reorderMenu = new OO.ui.MenuSelectWidget( {
		classes: [ 'flow-ui-latestTopicsWidget-menu' ],
		items: [ this.reorderMenuOptionWidget ],
		widget: this.button
	} );

	// Events
	this.reorderMenu.connect( this, { choose: 'onReorderMenuChoose' } );
	this.button.connect( this, { click: 'onButtonClick' } );

	// Initialize
	this.toggleOrderType( config.defaultSort || 'newest' );
	this.$element
		.addClass( 'flow-ui-latestTopicsWidget' )
		.append(
			this.button.$element,
			this.reorderMenu.$element
		);
};

/* Initialization */

OO.inheritClass( mw.flow.ui.ReorderTopicsWidget, OO.ui.Widget );

/* Events */

/**
 * Change the selected order of the topics.
 * The value toggles between 'newest' and 'updated'
 *
 * @event reorder
 * @param {string} orderType Topic order type
 */

/* Methods */

/**
 * Respond to button click
 */
mw.flow.ui.ReorderTopicsWidget.prototype.onButtonClick = function () {
	this.reorderMenu.toggle();
};

/**
 * Respond to menu choose. This is a technicality only, as there is always
 * only one menu option to choose from that toggles its value.
 */
mw.flow.ui.ReorderTopicsWidget.prototype.onReorderMenuChoose = function () {
	this.toggleOrderType();
	this.board.reset( this.getOrderType() );
};

/**
 * Get reorder type - 'newest' or 'updated'
 *
 * @return {string} Reorder type
 */
mw.flow.ui.ReorderTopicsWidget.prototype.getOrderType = function () {
	return this.orderType;
};

/**
 * Toggle reorder type between 'newest' and 'updated'
 *
 * @param {string} [type] Reorder type
 */
mw.flow.ui.ReorderTopicsWidget.prototype.toggleOrderType = function ( type ) {
	if ( this.orderType !== type ) {
		this.orderType = type || { newest: 'updated', updated: 'newest' }[ this.orderType ];

		// Change button label
		this.button.setLabel( this.messages[this.orderType] );

		// Change reorder menu option label
		this.reorderMenuOptionWidget.setLabel(
			this.messages[ { newest: 'updated', updated: 'newest' }[ this.orderType ] ]
		);

		this.board.setSortOrder( type );

		// Change the icon
		// TODO: This seems to not be working properly in OOUI itself
		// this.reorderMenuOptionWidget.setIcon( type === 'newest' ? 'beta' : 'clock' );

		// Emit event
		this.emit( 'reorder', this.orderType );
	}
};
