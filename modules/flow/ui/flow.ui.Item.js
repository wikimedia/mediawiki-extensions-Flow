/**
 * Flow item.
 *
 * @extends OO.ui.Element
 *
 * @param {flow.dm.Item} model Item model
 * @param {Object} [config] Configuration options
 */
flow.ui.Item = function FlowUiItem( model, config ) {
	// Parent constructor
	OO.ui.Element.call( this, config );

	// Properties
	this.model = model;
};

/* Inheritance */

OO.inheritClass( flow.ui.Item, OO.ui.Element );

/* Methods */

/**
 * Get the item model.
 *
 * @returns {flow.dm.Item} Model item
 */
flow.ui.Item.prototype.getModel = function() {
	return this.model;
};
