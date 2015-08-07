( function ( $ ) {
	/**
	 * Flow Board
	 *
	 * @class
	 * @extends mw.flow.dm.Item
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.dm.CategoryItem = function mwFlowDmCategoryItem( name, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.dm.CategoryItem.parent.call( this, config );

		this.setId( name );
		this.setExists( !!config.exists );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.dm.CategoryItem, mw.flow.dm.Item );

	mw.flow.dm.CategoryItem.prototype.setExists = function ( exists ) {
		this.categoryExists = exists;
	};

	mw.flow.dm.CategoryItem.prototype.exists = function () {
		return this.categoryExists;
	};
}( jQuery ) );
