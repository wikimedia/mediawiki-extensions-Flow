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
	mw.flow.ui.SearchInputWidget = function mwFlowUiSearchInputWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.SearchInputWidget.parent.call( this, config );

		// Mixin constructors
		OO.ui.mixin.LookupElement.call( this, config );

		this.promise = null;
		this.topics = [];
		this.searchTerm = '';
		this.pageTitle = config.pageTitle || '';

		this.noResultsOptionWidget = new OO.ui.OptionWidget( {
			data: null,
			label: 'no results'
		} );

		this.api = new mw.flow.dm.APIHandler( this.pageTitle && this.pageTitle.getPrefixedDb() );

		// Initialize
		this.$element
			.addClass( 'flow-ui-searchInputWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.SearchInputWidget, OO.ui.TextInputWidget );
	OO.mixinClass( mw.flow.ui.SearchInputWidget, OO.ui.mixin.LookupElement );

	mw.flow.ui.SearchInputWidget.prototype.getLookupRequest = function () {
		var abortablePromise,
			value = this.getValue(),
			pageName = this.pageTitle && this.pageTitle.getPrefixedDb();

		abortablePromise = this.api
			.getSearchResults( value, true, { page: pageName } );

		return abortablePromise
			.then( function ( result ) {
				return result.rows;
			} )
			.promise( { abort: abortablePromise.abort } );
	};

	/**
	 * Pre-process data returned by the request from #getLookupRequest.
	 *
	 * The return value of this function will be cached, and any further queries for the given value
	 * will use the cache rather than doing API requests.
	 *
	 * @param {Object[]} data Response from server
	 * @return {mw.flow.dm.Topic} Data model topics
	 */
	mw.flow.ui.SearchInputWidget.prototype.getLookupCacheDataFromResponse = function ( data ) {
		var i, len,
			topics = [];

		for ( i = 0, len = data.length; i < len; i++ ) {
			topics.push( new mw.flow.dm.Topic( data[i].postId, data[i] ) );
		}
		// Cache
		this.topics = topics;

		return this.topics;
	};

	/**
	 * Get list of menu items from a server response.
	 *
	 * @param {Object[]} data Query result
	 * @returns {OO.ui.MenuOptionWidget[]} Menu items
	 */
	mw.flow.ui.SearchInputWidget.prototype.getLookupMenuOptionsFromData = function ( topics ) {
		var i, len,
			menuOptionWidgets = [];

		if ( topics.length > 0 ) {
			console.log( topics );
			// Create result widgets
			for ( i = 0, len = topics.length; i < len; i++ ) {
				menuOptionWidgets.push( new OO.ui.MenuOptionWidget( {
					data: topics[i],
					label: topics[i].getContent()
				} ) );
			}
		}
		return menuOptionWidgets;
	};

}( jQuery ) );
