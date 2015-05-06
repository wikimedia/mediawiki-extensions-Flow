/**
 * Flow posts resource provider.
 *
 * @class
 * @extends mw.flow.dm.APIResultsProvider
 *
 * @constructor
 * @param {string} page The page associated with the topics
 * @param {string} apiurl The URL for the API
 * @param {Object} [config] Configuration options
 * @cfg {string} [scriptDirUrl] The url of the API script
 */
mw.flow.dm.APITopicsProvider = function MwFlowDmAPITopicsProvider( page, apiurl, config ) {
	config = config || {};

	this.page = page;

	// Parent constructor
	mw.flow.dm.APITopicsProvider.super.call(
		this,
		apiurl,
		$.extend( {
			staticParams: {
				action: 'flow',
				submodule: 'view-topiclist',
				page: this.page
			}
		}, config )
	);
};

/* Inheritance */
OO.inheritClass( mw.flow.dm.APITopicsProvider, mw.flow.dm.APIResultsProvider );

/* Methods */

/**
 * Call the API for search results.
 *
 * @param {number} [howMany] The number of results to retrieve
 * @return {jQuery.Promise} Promise that resolves with an array of objects that contain
 *  the fetched data.
 */
mw.flow.dm.APITopicsProvider.prototype.fetchAPIresults = function ( howMany ) {
	var xhr, apiCallConfig,
		provider = this;

	howMany = howMany || this.getDefaultFetchLimit();

	apiCallConfig = $.extend(
		{},
		this.getUserParams(),
		{
			vtloffset: this.getOffset(),
			vtllimit: howMany
		} );

	xhr = new mw.Api().get( $.extend( this.getStaticParams(), apiCallConfig ), this.getAjaxSettings() );
	return xhr
		.then( function ( data ) {
			var i, len, post,
				result = {},
				topiclist = OO.getProp( data.flow, 'view-topiclist', 'result', 'topiclist' );

			if ( data.error ) {
				provider.toggleDepleted( true );
				return [];
			}

			// If there are no more posts, mark the board to prevent redundant api calls
			if ( Object.keys( topiclist.roots ).length < howMany ) {
				provider.toggleDepleted( true );
			} else {
				provider.setOffset( provider.getOffset() + howMany );
			}

			// Connect the information to the topic
			for ( i = 0, len = topiclist.roots.length; i < len; i++ ) {
				post = topiclist.posts[ topiclist.roots[i] ];
				result[ topiclist.roots[i] ] = topiclist.revisions[ post ];
			}

			return result;
		} )
		.promise( { abort: xhr.abort } );
};
