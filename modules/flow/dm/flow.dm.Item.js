/**
 * Item.
 *
 * @mixins OO.EventEmitter
 */
flow.dm.Item = function FlowDmItem() {
	// Mixin constructors
	OO.EventEmitter.call( this );
};

/* Inheritance */

OO.mixinClass( flow.dm.Item, OO.EventEmitter );
