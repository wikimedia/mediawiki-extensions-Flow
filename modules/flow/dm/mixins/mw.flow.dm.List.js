/**
 * Flow List mixin
 * Must be mixed into an mw.flow.dm.Item element
 *
 * @mixin
 * @abstract
 * @constructor
 * @param {Object} config Configuration options
 */
mw.flow.dm.List = function mwFlowDmList( config ) {
	// Configuration initialization
	config = config || {};

	this.items = [];
	// Store references to items by their ids
	this.references = {};
};

/* Events */

/**
 * @event add Items have been added
 * @param {flow.dm.Item[]} items Added items
 * @param {number} index Index items were added at
 */

/**
 * @event remove Items have been removed
 * @param {flow.dm.Item[]} items Removed items
 */

/* Methods */

/**
 * Get all items
 *
 * @return {mw.flow.dm.Item[]} Items in the list
 */
mw.flow.dm.List.prototype.getItems = function () {
	return this.items.slice( 0 );
};

/**
 * Get an item by its id
 * @param {string} id Item id
 * @return {mw.flow.dm.Item} Item
 */
mw.flow.dm.List.prototype.getItemById = function ( id ) {
	return this.references[ id ];
};

/**
 * Get number of items
 *
 * @return {number} Number of items in the list
 */
mw.flow.dm.List.prototype.getItemCount = function () {
	return this.items.length;
};

/**
 * Add items
 *
 * @param {mw.flow.dm.Item[]} items Items to add
 * @param {number} index Index to add items at
 * @chainable
 */
mw.flow.dm.List.prototype.addItems = function ( items, index ) {
	var i, at, len, item, currentIndex;

	// Support adding existing items at new locations
	for ( i = 0, len = items.length; i < len; i++ ) {
		item = items[i];
		// Check if item exists then remove it first, effectively "moving" it
		currentIndex = this.items.indexOf( item );
		if ( currentIndex >= 0 ) {
			this.removeItems( [ item ] );
			// Adjust index to compensate for removal
			if ( currentIndex < index ) {
				index--;
			}
		}

		// Add by reference
		this.references[ items[i].getId() ] = items[i];
	}

	if ( index === undefined || index < 0 || index >= this.items.length ) {
		at = this.items.length;
		this.items.push.apply( this.items, items );
	} else if ( index === 0 ) {
		at = 0;
		this.items.unshift.apply( this.items, items );
	} else {
		at = index;
		this.items.splice.apply( this.items, [ index, 0 ].concat( items ) );
	}
	this.emit( 'add', items, at );

	return this;
};

/**
 * Remove items
 *
 * @param {mw.flow.dm.Item[]} items Items to remove
 * @chainable
 */
mw.flow.dm.List.prototype.removeItems = function ( items ) {
	var i, len, item, index,
		removed = [];

	// Remove specific items
	for ( i = 0, len = items.length; i < len; i++ ) {
		item = items[i];
		index = this.items.indexOf( item );
		if ( index !== -1 ) {
			removed = removed.concat( this.items.splice( index, 1 ) );
			item.disconnect( this );
			// Remove reference by Id
			delete this.references[ item.getId() ];
		}
	}
	this.emit( 'remove', removed );

	return this;
};

/**
 * Clear all items
 */
mw.flow.dm.List.prototype.clearItems = function () {
	var i, len;

	// Disconnect events
	for ( i = 0, len = this.items.length; i < len; i++ ) {
		this.items[i].disconnect( this );
	}

	// Clear all references
	this.references = {};
	this.items = [];

	this.emit( 'clear' );

	return this;
};
