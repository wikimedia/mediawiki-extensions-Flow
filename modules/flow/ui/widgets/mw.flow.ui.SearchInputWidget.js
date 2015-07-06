( function ( $ ) {
	/**
	 * Flow search input widget
	 *
	 * @class
	 * @extends OO.ui.TextInputWidget
	 * @mixin OO.ui.mixin.LookupElement
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
		this.topiclist = null;
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
		var value = this.getValue(),
			pageName = this.pageTitle && this.pageTitle.getPrefixedDb();

		this.topiclist = null;

		return this.api
			.getSearchResults( value, { page: pageName } );
	};

	/**
	 * Pre-process data returned by the request from #getLookupRequest.
	 *
	 * The return value of this function will be cached, and any further queries for the given value
	 * will use the cache rather than doing API requests.
	 *
	 * @param {Object[]} topiclist Response from server
	 * @return {mw.flow.dm.Topic} Data model topics
	 */
	mw.flow.ui.SearchInputWidget.prototype.getLookupCacheDataFromResponse = function ( topiclist ) {
		// Cache
		this.topiclist = topiclist;

		return mw.flow.dm.Topic.static.extractTopicsFromAPI( topiclist );
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

		this.topicIds = [];
		if ( topics.length > 0 ) {
			console.log( topics );
			// Create result widgets
			for ( i = 0, len = topics.length; i < len; i++ ) {
				this.topicIds.push( topics[ i ].getId() );
				menuOptionWidgets.push( new OO.ui.MenuOptionWidget( {
					data: topics[i],
					label: topics[i].getContent()
				} ) );
			}
		}
		return menuOptionWidgets;
	};

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.SearchInputWidget.prototype.onLookupMenuItemChoose = function ( item ) {
		var id = item.getData() && item.getData().getId();

		this.loadResultsToBoard();

		if ( id ) {
			this.emit( 'goToTopic', item.getData().getId() );
		}
	};

	/**
	 * Load the results as a new board.
	 * This entire method is one big uberhack to connect to the old system. When we have
	 * oouified widgets for topic and posts, we'll be able to remove this bit.
	 *
	 * @param {Object} topiclist API response for topiclist
	 */
	mw.flow.ui.SearchInputWidget.prototype.loadResultsToBoard = function ( topiclist ) {
		var $replacement,
			// UBERHACK: Until we have topic and post widgets, we have to work with the current board
			// which means we have to fetch a board and work with the handlebars system.
			// This should be eliminated and burned when topic and replies are widgetized.
			flowBoard = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $( '.flow-board' ) );

		$replacement = $( flowBoard.constructor.static.TemplateEngine.processTemplateGetFragment(
			'flow_topiclist_loop.partial',
			this.topiclist
		) ).children();

		$( '.flow-topics' ).empty().append( $replacement );
		// Run loadHandlers
		flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );

		// TODO: Rebuild the dm.Board!
	};

}( jQuery ) );
