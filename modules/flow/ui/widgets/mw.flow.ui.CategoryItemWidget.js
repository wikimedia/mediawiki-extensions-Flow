( function ( $ ) {
	/**
	 * Flow board categories widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 * @mixin OO.ui.mixin.GroupElement
	 *
	 * @constructor
	 * @param {Object} [config]
	 * @cfg {boolean} [exists] Category page exists on this wiki
	 */
	mw.flow.ui.CategoryItemWidget = function mwFlowUiCategoryItemWidget( categoryName, config ) {
		var $link;

		config = config || {};

		// Parent constructor
		mw.flow.ui.CategoryItemWidget.parent.call( this, config );

		this.name = categoryName;
		this.title = mw.Title.newFromText( this.name, mw.config.get( 'wgNamespaceIds' ).category );
		this.exists = config.exists;

		$link = $( '<a>' )
			.attr( 'href', mw.util.getUrl( this.title.getPrefixedDb() ) )
			.attr( 'title', this.exists ? this.name : mw.msg( 'red-link-title', this.name ) )
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
