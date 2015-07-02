( function ( $ ) {
	/**
	 * Flow search results widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {string} term Search term
	 * @param {Object} [config]
	 * @cfg {string[]} [topicIds] An array of topic ids to display
	 */
	mw.flow.ui.SearchResultsWidget = function mwFlowUiSearchResultsWidget( term, config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.SearchResultsWidget.parent.call( this, config );

		// HACK: Load the template engine so we can use handlebars
		// When topics are widgets, this will not be necessary
		this.templateEngine = new mw.flow.FlowHandlebars( mw.flow.StorageEngine );

		this.term = term;
		this.api = new mw.flow.dm.APIHandler();

		this.termLabel = new OO.ui.LabelWidget( {
			label: mw.msg( 'flow-search-results-title', term ),
			classes: [ 'flow-ui-searchResultsWidget-title' ]
		} );
		this.setSearchTerm( term );

		// Spinner
		this.loadingLabel = new OO.ui.LabelWidget( {
			classes: [ 'flow-load-more' ]
		} )
			// Start hidden
			.toggle( false );

		this.$topics = $( '<div>' )
			.addClass( 'flow-ui-searchResultsWidget-topiclist' )
			// HACK: Make this whole thing work with handlebars
			.addClass( 'flow-topics' );

		this.loadTopics( config.topicIds || [] );

		// Initialize
		this.$element
			.append(
				this.termLabel.$element,
				this.loadingLabel.$element,
				$( '<div>' )
					.addClass( 'flow-board' )
					.append( this.$topics )
			)
			.addClass( 'flow-ui-searchResultsWidget' )
			.css( 'height', $( window ).height() )
			// HACK! Without these, emitWithReturn doesn't work
			.addClass( 'flow-component' )
			.data( 'flow-id', Math.random() );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.SearchResultsWidget, OO.ui.Widget );

	/**
	 * Set the search term for these results
	 *
	 * @param {string} term Search term
	 */
	mw.flow.ui.SearchResultsWidget.prototype.setSearchTerm = function ( term ) {
		if ( this.term !== term ) {
			this.term = term;
			this.termLabel.setLabel( mw.msg( 'flow-search-results-title', term ) );
		}
	};

	mw.flow.ui.SearchResultsWidget.prototype.scrollToTopic = function ( topicId ) {
		var $topic = this.$element.find( '#flow-topic-' + topicId );

		if ( $topic.length ) {
			this.$element.scrollTop( this.$element.scrollTop() + $topic.position().top );
		}
	};

	/**
	 * Set the flowBoard to work with.
	 * THIS IS A HACK! This is meant to make sure we can work with handlebars
	 * and transform the fetched topics to interactive content. When topics are
	 * widgets, this will no longer be necessary.
	 *
	 * @param {flowBoard} board Flow board
	 */
	mw.flow.ui.SearchResultsWidget.prototype.setFlowBoard = function ( board ) {
		this.flowBoard = board;
	};

	/**
	 * Load given topics into the search display.
	 *
	 * @param {string[]} topicIds An array of topic ids
	 */
	mw.flow.ui.SearchResultsWidget.prototype.loadTopics = function ( topicIds, topicToScrollTo ) {
		var i, len, promise,
			widget = this,
			promises = [];

		if ( topicIds.length === 0 ) {
			return null;
		}

		this.loadingLabel.toggle( true );
		widget.$topics.addClass( 'oo-ui-element-hidden' );

		for ( i = 0, len = topicIds.length; i < len; i++ ) {
			promise = this.api.getTopic( topicIds[ i ] );
			promises.push( promise );
		}

		$.when.apply( $, promises )
			.then( function () {
				var $replacement,
					results = Array.prototype.slice.call( arguments );

				for ( i = 0, len = results.length; i < len; i++ ) {
					// Render using handlebars
					// Update view of the full topic
					$replacement = $( widget.templateEngine.processTemplateGetFragment(
						'flow_topiclist_loop.partial',
						results[ i ].topic
					) ).children();
					widget.$topics.append( $replacement );
					// widget.flowBoard.emitWithReturn( 'makeContentInteractive', $replacement );
				}
			} )
			.then( function () {
				widget.loadingLabel.toggle( false );
				widget.$topics.removeClass( 'oo-ui-element-hidden' );
			} )
			.then( function () {
				if ( topicToScrollTo ) {
					widget.scrollToTopic( topicToScrollTo );
				}
			} );
	};
}( jQuery ) );
