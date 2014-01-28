/**
 * Flow list.
 *
 * @extends flow.ui.Item
 * @mixins OO.ui.GroupElement
 *
 * @param {flow.dm.List} model List model
 * @param {Object} [config] Configuration options
 */
flow.ui.List = function FlowUiList( model, config ) {
	// Parent constructor
	flow.ui.Item.call( this, model, config );

	// Mixin constructors
	OO.ui.GroupElement.call( this, this.$( '<div>' ) );

	// Events
	this.model.connect( this, {
		'add': 'onModelAddItems',
		'remove': 'onModelRemoveItems'
	} );

	// Initialization
	this.$element.append( this.$group );
	this.addItems( this.createItems( model.getItems() ) );
};

/* Inheritance */

OO.inheritClass( flow.ui.List, flow.ui.Item );

OO.mixinClass( flow.ui.List, OO.ui.GroupElement );

/* Static Properties */

/**
 * @property Constructor to use when creating items
 * @inhertable
 * @static
 * @type {Function}
 */
flow.ui.List.static.itemConstructor = flow.ui.Item;

/* Methods */

/**
 * Handle add item events.
 *
 * @param {flow.ui.Item[]} items Added items
 * @param {number} index Index items were added at
 */
flow.ui.List.prototype.onModelAddItems = function ( items, index ) {
	this.addItems( this.createItems( items ), index );
};

/**
 * Handle remove item events.
 *
 * @param {flow.ui.Item[]} items Removed items
 */
flow.ui.List.prototype.onModelRemoveItems = function ( items ) {
	this.removeItems( this.findItems( items ) );
};

/**
 * Create items from models.
 *
 * @param {flow.dm.Item[]} models Models to create items from
 * @returns {flow.dm.Item} Items for models
 */
flow.ui.List.prototype.createItems = function( models ) {
	var i, len,
		items = [];

	for ( i = 0, len = models.length; i < len; i++ ) {
		items.push( new this.constructor.static.itemConstructor( models[i] ) );
	}

	return items;
};

/**
 * Find items from models.
 *
 * @param {flow.dm.Item[]} models Models to create items from
 * @returns {flow.dm.Item} Items for models
 */
flow.ui.List.prototype.findItems = function ( models ) {
	var i, len, item,
		items = [];

	for ( i = 0, len = this.items.length; i < len; i++ ) {
		item = this.items[i];
		if ( models.indexOf( item.getModel() ) !== -1 ) {
			items.push( item );
		}
	}

	return items;
};
