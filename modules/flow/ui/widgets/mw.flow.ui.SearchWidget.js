( function ( $ ) {
	/**
	 * Flow search widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config]
	 * @cfg {mw.Title} [pageTitle] Page title
	 */
	mw.flow.ui.SearchWidget = function mwFlowUiSearchWidget( overlay, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.SearchWidget.parent.call( this, config );

		this.searchTerm = '';
		this.pageTitle = config.pageTitle || '';

		this.search = new mw.flow.ui.SearchInputWidget( overlay, config );

		// Initialize
		this.$element
			.append( this.search.$element )
			.addClass( 'flow-ui-searchWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.SearchWidget, OO.ui.Widget );

	mw.flow.ui.SearchWidget.prototype.setFlowBoard = function ( board ) {
		this.search.setFlowBoard( board );
	};
}( jQuery ) );
