/**
 * Flow Item
 *
 * @abstract
 * @mixins OO.EventEmitter
 */
mw.flow.dm.Item = function mwFlowDmItem() {
	// Mixin constructor
	OO.EventEmitter.call( this );

	this.id = null;
};

/* Inheritance */

OO.mixinClass( mw.flow.dm.Item, OO.EventEmitter );

/**
 * Get item id
 * @return {string} Item Id
 */
mw.flow.dm.Item.prototype.getId = function () {
	return this.id;
};

/**
 * Set item id
 * @param {string} id Item Id
 */
mw.flow.dm.Item.prototype.setId = function ( id ) {
	this.id = id;
};
