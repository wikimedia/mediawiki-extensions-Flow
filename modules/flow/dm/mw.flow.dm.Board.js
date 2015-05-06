( function ( $ ) {
	/**
	 * Flow Board
	 *
	 * @constructor
	 *
	 * @extends mw.flow.dm.Item
	 * @mixins mw.flow.dm.List
	 *
	 * @param {Object} data API data to build board with
	 * @param {string} data.id Board Id
	 * @param {mw.Title} data.pageTitle Current page title
	 * @param {boolean} [data.isDeleted] Board is deleted
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.dm.Board = function mwFlowDmBoard( data, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.dm.Board.super.call( this, config );

		// Mixing constructor
		mw.flow.dm.List.call( this, config );

		// TODO: Fill this stuff in properly
		this.setId( data.id );
		this.pageTitle = data.pageTitle;
		this.deleted = !!data.isDeleted;

		// TODO: Aggregate events for all topics so we can let
		// widgets listen to events like 'content' change
		// (For example, the ToC widget should respond and update
		// itself in case a topic title changes)
	};

	/* Initialization */

	OO.inheritClass( mw.flow.dm.Board, mw.flow.dm.Item );
	OO.mixinClass( mw.flow.dm.Board, mw.flow.dm.List );

	/* Events */

	/**
	 * Board description changes
	 *
	 * @event descriptionChange
	 * @param {mw.flow.dm.BoardDescription} New description
	 */

	/**
	 * Board topics are reset
	 *
	 * @event reset
	 * @param {string} order The order of the topics; 'newest' or 'updated'
	 */

	/* Methods */

	/**
	 * Get a hash object representing the current state
	 * of the Board
	 *
	 * @return {Object} Hash object
	 */
	mw.flow.dm.Board.prototype.getHash = function () {
		return $.extend( {
			id: this.getId(),
			isDeleted: this.isDeleted(),
			pagePrefixedDb: this.getPageTitle().getPrefixedDb(),
			topicCount: this.getItemCount(),
			description: this.getDescription() && this.getDescription().getHash()
		},
			// Parent
			mw.flow.dm.Board.super.prototype.getHash.call( this )
		);
	};

	/**
	 * Check if the board is in a deleted page
	 *
	 * @return {boolean} Board is in a deleted page
	 */
	mw.flow.dm.Board.prototype.isDeleted = function () {
		return this.deleted;
	};

	/**
	 * Get page title
	 *
	 * @return {mw.Title} Page title
	 */
	mw.flow.dm.Board.prototype.getPageTitle = function () {
		return this.pageTitle;
	};

	/**
	 * Get board description
	 *
	 * @return {mw.flow.dm.BoardDescription} Board description
	 */
	mw.flow.dm.Board.prototype.getDescription = function () {
		return this.description;
	};

	/**
	 * Set board description
	 *
	 * @param {mw.flow.dm.BoardDescription} Board description
	 * @fires descriptionChange
	 */
	mw.flow.dm.Board.prototype.setDescription = function ( desc ) {
		this.description = desc;
		this.emit( 'descriptionChange', this.description );
	};

	/**
	 * Reset the board
	 *
	 * @param {string} order The order of the topics; 'newest' or 'updated'
	 * @fires reset
	 */
	mw.flow.dm.Board.prototype.reset = function ( order ) {
		this.clearItems();
		this.emit( 'reset', order );
	};
}( jQuery ) );
