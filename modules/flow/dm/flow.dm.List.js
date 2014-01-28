/**
 * List of items.
 *
 * @extends flow.dm.Item
 */
flow.dm.List = function FlowDmList() {
	// Parent constructor
	flow.dm.Item.call( this );

	// Properties
	this.items = [];
};

/* Inheritance */

OO.inheritClass( flow.dm.List, flow.dm.Item );

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
 * Get all items.
 *
 * @returns {flow.dm.Item[]} Items in list
 */
flow.dm.List.prototype.getItems = function () {
	return this.items.slice( 0 );
};

/**
 * Get number of items.
 *
 * @returns {number} Number of items
 */
flow.dm.List.prototype.getItemCount = function () {
	return this.items.length;
};

/**
 * Add items.
 *
 * @param {flow.dm.Item[]} items Items to add
 * @param {number} index Index to add items at
 * @chainable
 */
flow.dm.List.prototype.addItems = function ( items, index ) {
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
 * Items
 *
 * @param {flow.dm.Item[]} items List of items to remove
 * @chainable
 */
flow.dm.List.prototype.removeItems = function ( items ) {
	var i, len, item, index,
		removed = [];

	// Remove specific items
	for ( i = 0, len = items.length; i < len; i++ ) {
		item = items[i];
		index = this.items.indexOf( item );
		if ( index !== -1 ) {
			removed = removed.concat( this.items.splice( index, 1 ) );
		}
	}
	this.emit( 'remove', removed );

	return this;
};

/**
 * Clear all items.
 *
 * @chainable
 */
flow.dm.List.prototype.clearItems = function () {
	this.emit( 'remove', this.items.slice( 0 ) );
	this.items = [];

	return this;
};
