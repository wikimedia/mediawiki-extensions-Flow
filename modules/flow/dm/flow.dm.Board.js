/**
 * Flow Board.
 *
 * @extends flow.dm.List
 *
 * @param {Object} data API data to build board with
 */
flow.dm.Board = function FlowDmBoard( data ) {
	// Parent constructor
	flow.dm.List.call( this );

	// Properties
	this.id = data.id;
	this.header = data.header;
};

/* Inheritance */

OO.inheritClass( flow.dm.Board, flow.dm.List );

/* Events */

/**
 * @event change Board has changed
 */

/* Methods */

/**
 * Get board ID.
 *
 * @returns {string} UUID
 */
flow.dm.Board.prototype.getId = function () {
	return this.id;
};

/**
 * Get header HTML.
 *
 * @returns {string} Header HTML
 */
flow.dm.Board.prototype.getHeader = function () {
	return this.header;
};

/**
 * Get header HTML.
 *
 * @param {string} header Header HTML
 * @chainable
 */
flow.dm.Board.prototype.setHeader = function ( header ) {
	this.header = header;
	this.emit( 'change' );
	return this;
};
