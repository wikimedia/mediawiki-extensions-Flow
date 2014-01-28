/**
 * Flow Board.
 *
 * @extends flow.ui.List
 *
 * @param {flow.dm.Board} model Board model
 * @param {Object} [config] Configuration options
 */
flow.ui.Board = function FlowUiBoard( model, config ) {
	// Parent constructor
	flow.ui.List.call( this, model, config );

	// Properties
	this.$header = this.$( '<div>' );

	// Events
	this.model.connect( this, { 'change': 'setup' } );

	// Initialization
	this.$group.addClass( 'flow-ui-board-topics' );
	this.$header.addClass( 'flow-ui-board-header' );
	this.$element
		.addClass( 'flow-ui-board' )
		.append( this.$header, this.$group );
	this.setup();
};

/* Inheritance */

OO.inheritClass( flow.ui.Board, flow.ui.List );

/* Static Properties */

flow.ui.Board.static.itemConstructor = flow.ui.Topic;

/* Methods */

/**
 * Setup board.
 */
flow.ui.Board.prototype.setup = function () {
	this.$header.html( this.model.getHeader() );
};
