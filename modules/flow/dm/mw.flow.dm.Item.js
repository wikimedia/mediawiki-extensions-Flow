/**
 * Flow Item
 *
 * @abstract
 * @mixins OO.EventEmitter
 */
mw.flow.dm.Item = function mwFlowDmItem() {
	// Mixin constructor
	OO.EventEmitter.call( this );
};

/* Inheritance */

OO.mixinClass( mw.flow.dm.Item, OO.EventEmitter );
