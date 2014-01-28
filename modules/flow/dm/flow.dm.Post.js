/**
 * Flow Post.
 *
 * @extends flow.dm.Item
 *
 * @param {Object} data API data to build post with
 */
flow.dm.Post = function FlowDmPost( data ) {
	// Parent constructor
	flow.dm.Item.call( this );

	// Properties
	this.id = data.id;
	this.creator = data.creator;
	this.timestamp = data.timestamp;
	this.content = data.content;
};

/* Inheritance */

OO.inheritClass( flow.dm.Post, flow.dm.Item );

/**
 * @event change Post has changed
 */

/* Methods */

/**
 * Get post ID.
 *
 * @returns {string} UUID
 */
flow.dm.Post.prototype.getId = function () {
	return this.id;
};

/**
 * Get username of post creator.
 *
 * @returns {string} Username of post creator.
 */
flow.dm.Post.prototype.getCreator = function () {
	return this.creator;
};

/**
 * Get latest timestamp.
 *
 * @returns {number} Latest timestamp
 */
flow.dm.Post.prototype.getTimestamp = function () {
	return this.timestamp;
};

/**
 * Set latest timestamp.
 *
 * @param {number} timestamp Latest timestamp
 * @chainable
 */
flow.dm.Post.prototype.setTimestamp = function ( timestamp ) {
	this.timestamp = timestamp;
	this.emit( 'change' );
	return this;
};

/**
 * Get content.
 *
 * @returns {string} HTML content
 */
flow.dm.Post.prototype.getContent = function () {
	return this.content;
};

/**
 * Set content.
 *
 * @param {string} content HTML content
 * @chainable
 */
flow.dm.Post.prototype.setContent = function ( content ) {
	this.content = content;
	this.emit( 'change' );
	return this;
};
