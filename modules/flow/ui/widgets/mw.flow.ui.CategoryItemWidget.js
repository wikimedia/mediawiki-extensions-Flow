( function ( $ ) {
	/**
	 * Flow board categories widget
	 *
	 * @extends OO.ui.Widget
	 * @mixin OO.ui.mixin.GroupElement
	 *
	 * @constructor
	 * @param {Object} [config]
	 *
	 */
	mw.flow.ui.CategoryItemWidget = function mwFlowUiCategoryItemWidget( categoryName, config ) {
		var $link;

		config = config || {};

		// Parent constructor
		mw.flow.ui.CategoryItemWidget.parent.call( this, config );

		this.name = categoryName;
		this.exists = config.exists;

		$link = $( '<a>' )
			.attr( 'href', mw.util.getUrl( this.name ) )
			.attr( 'title', this.exists ? this.name : mw.msg( 'red-link-title', this.name ) )
			.text( this.name )
			.toggleClass( 'new', !this.exists );

		this.$element
			.addClass( 'flow-ui-categoryItemWidget' )
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
