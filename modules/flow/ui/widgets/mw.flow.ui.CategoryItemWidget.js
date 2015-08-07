( function ( $ ) {
	/**
	 * Flow board categories widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 * @mixin OO.ui.mixin.GroupElement
	 *
	 * @constructor
	 * @param {mw.flow.dm.CategoryItem} categoryModel Category item model
	 * @param {Object} [config]
	 * @cfg {boolean} [exists] Category page exists on this wiki
	 */
	mw.flow.ui.CategoryItemWidget = function mwFlowUiCategoryItemWidget( categoryModel, config ) {
		var prefixedName, $link;

		config = config || {};

		// Parent constructor
		mw.flow.ui.CategoryItemWidget.parent.call( this, config );

		this.model = categoryModel;
		this.name = this.model.getId();
		this.title = mw.Title.newFromText( this.name, mw.config.get( 'wgNamespaceIds' ).category );
		this.exists = this.model.exists();
		prefixedName = this.title && this.title.getPrefixedDb() || this.name;

		$link = $( '<a>' )
			.attr( 'href', mw.util.getUrl( prefixedName ) )
			.attr( 'title', this.exists ? prefixedName : mw.msg( 'red-link-title', prefixedName ) )
			.text( this.name )
			.toggleClass( 'new', !this.exists );

		this.$element
			.addClass( 'flow-ui-categoryItemWidget flow-board-header-category-item' )
			.append( $link );
	};

	OO.inheritClass( mw.flow.ui.CategoryItemWidget, OO.ui.Widget );

	/**
	 * Get the category data
	 *
	 * @return {string} Category name
	 */
	mw.flow.ui.CategoryItemWidget.prototype.getData = function () {
		return this.name;
	};
}( jQuery ) );
