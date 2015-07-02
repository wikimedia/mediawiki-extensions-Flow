( function ( $ ) {
	/**
	 * Flow search widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config]
	 * @cfg {mw.Title} [pageTitle] Page title
	 */
	mw.flow.ui.SearchWidget = function mwFlowUiSearchWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.SearchWidget.parent.call( this, config );

		this.searchTerm = '';
		this.pageTitle = config.pageTitle || '';

		this.search = new mw.flow.ui.SearchInputWidget( config );
		// this.noResultsOptionWidget = new OO.ui.OptionWidget( {
		// 	data: null,
		// 	label: 'no results'
		// } );

		// this.api = new mw.flow.dm.APIHandler( this.pageTitle && this.pageTitle.getPrefixedDb() );

		// Initialize
		this.$element
			.append( this.search.$element )
			.addClass( 'flow-ui-searchWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.SearchWidget, OO.ui.Widget );

	/* Methods */

	// mw.flow.ui.SearchWidget.prototype.onQueryChange = function () {
	// 	var pageName = this.pageTitle && this.pageTitle.getPrefixedDb(),
	// 		widget = this;

	// 	// Parent
	// 	mw.flow.ui.SearchWidget.parent.prototype.onQueryChange.call( this );

	// 	this.query.pushPending();
	// 	this.api
	// 		.getSearchResults( this.query.getValue(), true, { page: pageName } )
	// 		.then( this.processResults.bind( this ) )
	// 		.then( function () {
	// 			widget.query.popPending();
	// 		} );
	// };

	// mw.flow.ui.SearchWidget.prototype.processResults = function ( result ) {
	// 	var i, len, resultWidget,
	// 		optionWidgets = [];

	// 	if ( result.total > 0 ) {
	// 		console.log( result );
	// 		// Create result widgets
	// 		for ( i = 0, len = result.rows.length; i < len; i++ ) {
	// 			optionWidgets.push( new OO.ui.OptionWidget( {
	// 				data: result.rows[i],
	// 				label: OO.getProp( result.rows[i], 'properties', 'topic-of-post', 'plaintext' )
	// 			} ) );
	// 		}

	// 		this.results.addItems( optionWidgets );
	// 	} else {
	// 		// TODO: Indicate no result
	// 	}
	// };
}( jQuery ) );
