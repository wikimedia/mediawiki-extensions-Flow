( function ( $ ) {
	/**
	 * Flow board categories widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 * @mixins OO.ui.mixin.GroupElement
	 *
	 * @constructor
	 * @param {mw.flow.dm.Board} model Board model
	 * @param {Object} [config]
	 *
	 */
	mw.flow.ui.CategoriesWidget = function mwFlowUiCategoriesWidget( model, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.CategoriesWidget.parent.call( this, config );

		// Mixin constructor
		OO.ui.mixin.GroupElement.call( this, config );

		this.model = model;
		this.model.connect( this, {
			addCategories: 'onModelAddCategories',
			removeCategories: 'onModelRemoveCategories',
			clearCategories: 'onModelClearCategories'
		} );

		// Initialize
		this.$element
			.append( this.$group.addClass( 'flow-board-header-category-list' ) )
			.addClass( 'flow-ui-categoriesWidget flow-board-header-category-view' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.CategoriesWidget, OO.ui.Widget );
	OO.mixinClass( mw.flow.ui.CategoriesWidget, OO.ui.mixin.GroupElement );

	/**
	 * Respond to a change of categories in the board model
	 *
	 * @param {mw.flow.dm.CategoryItem[]} categories Added categories
	 */
	mw.flow.ui.CategoriesWidget.prototype.onModelAddCategories = function ( categories ) {
		var i, len,
			widgets = [];

		for ( i = 0, len = categories.length; i < len; i++ ) {
			widgets.push( new mw.flow.ui.CategoryItemWidget( categories[ i ].getId(), {
				exists: categories[ i ].exists()
			} ) );
		}

		this.addItems( widgets );
	};

	/**
	 * Respond to removing categories from the model
	 *
	 * @param {mw.flow.dm.CategoryItem[]} categories Removed categories
	 */
	mw.flow.ui.CategoriesWidget.prototype.onModelRemoveCategories = function ( categories ) {
		var i, len,
			widgets = [];

		for ( i = 0, len = categories.length; i < len; i++ ) {
			widgets.push( this.getItemFromData( categories[ i ].getId() ) );
		}

		this.removeItems( widgets );
	};

	/**
	 * Respond to clearing all categories from the model
	 */
	mw.flow.ui.CategoriesWidget.prototype.onModelClearCategories = function () {
		this.clearItems();
	};
}( jQuery ) );
