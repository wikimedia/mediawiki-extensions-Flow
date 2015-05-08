( function ( $ ) {
	/**
	 * Flow Board
	 *
	 * @class
	 * @extends mw.flow.dm.Item
	 * @mixins mw.flow.dm.List
	 *
	 * @constructor
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

		// Mixin constructor
		mw.flow.dm.List.call( this, config );

		// TODO: Fill this stuff in properly
		this.setId( data.id );
		this.pageTitle = data.pageTitle;
		this.deleted = !!data.isDeleted;
		this.sort = data.defaultSort || 'newest';

		this.aggregate( { contentChange: 'topicContentChange' } );
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

	/**
	 * One of the board's topic content changed
	 *
	 * @event topicContentChange
	 * @param {string} topicId Topic UUID
	 * @param {string} content Topic content
	 * @param {string} format Content format
	 */

	/* Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.dm.Board.prototype.getHashObject = function () {
		return $.extend( {
			isDeleted: this.isDeleted(),
			pagePrefixedDb: this.getPageTitle().getPrefixedDb(),
			topicCount: this.getItemCount(),
			description: this.getDescription() && this.getDescription().getHashObject()
		},
			// Parent
			mw.flow.dm.Board.super.prototype.getHashObject.call( this )
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
	 * Get board sort order, 'newest' or 'updated'
	 *
	 * @return {string} Board sort order
	 */
	mw.flow.dm.Board.prototype.getSortOrder = function () {
		return this.sort;
	};

	/**
	 * Set board sort order, 'newest' or 'updated'
	 *
	 * @param {string} Board sort order
	 * @fires sortOrderChange
	 */
	mw.flow.dm.Board.prototype.setSortOrder = function ( order ) {
		if ( this.sort !== order ) {
			this.sort = order;
			this.emit( 'sortOrderChange', order );
		}
	};


	/**
	 * Get the last offset for the API's offsetId
	 */
	mw.flow.dm.Board.prototype.getOffsetId = function () {
		var topics = this.getItems();

		return topics.length > 0 ?
			topics[ topics.length - 1 ].getId() :
			null;
	};

	/**
	 * Get the last offset for the API's offset timestamp
	 */
	mw.flow.dm.Board.prototype.getOffset = function () {
		var topics = this.getItems();

		return topics.length > 0 ?
			topics[ topics.length - 1 ].getTimestamp() :
			null;
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
