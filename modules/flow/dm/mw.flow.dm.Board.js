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
 * @param {string} [data.highlight] An Id of a highlighted post, if exists
 * @param {Object} [config] Configuration options
 * @cfg {number} [tocPostsLimit] Post count for the table of contents. This is also
 *  used as the number of posts to fetch when more posts are requested for the table
 *  of contents. Defaults to 10
 * @cfg {number} [visibleItems] Number of visible posts on the page. This is also
 *  used as the number of posts to fetch when more posts are requested on scrolling
 *  the page. Defaults to the tocPostsLimit
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
	this.deleted = data.isDeleted;

	this.description = data.description;
};

/* Initialization */

OO.inheritClass( mw.flow.dm.Board, mw.flow.dm.Item );
OO.mixinClass( mw.flow.dm.Board, mw.flow.dm.List );

/**
 * Get a hash object representing the current state
 * of the Board
 *
 * @return {Object} Hash object
 */
mw.flow.dm.Board.prototype.getHash = function () {
	return {
		id: this.getId(),
		isDeleted: this.isDeleted(),
		pagePrefixedDb: this.getPageTitle().getPrefixedDb()
	};
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
 * Get board description
 *
 * @return {mw.flow.dm.BoardDescription} Board description
 * @fires description
 */
mw.flow.dm.Board.prototype.setDescription = function ( desc ) {
	this.description = desc;
	this.emit( 'description', this.description );
};
