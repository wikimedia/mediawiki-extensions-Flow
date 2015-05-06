/**
 * Resource Provider object.
 *
 * @class
 * @mixins OO.EventEmitter
 *
 * @constructor
 * @abstract
 * @param {string} apiurl The URL to the api
 * @param {Object} [config] Configuration options
 * @cfg {number} fetchLimit The default number of results to fetch
 * @cfg {string} lang The language of the API
 * @cfg {number} offset Initial offset, if relevant, to call results from
 * @cfg {Object} ajaxSettings The settings for the ajax call
 * @cfg {Object} staticParams The data parameters that are static and should
 *  always be sent to the API request, as opposed to user parameters.
 * @cfg {Object} userParams Initial user parameters to be sent as data to
 *  the API request. These can change per request, like the search query term
 *  or sizing parameters for images, etc.
 */
mw.flow.dm.APIResultsProvider = function VeDmResourceProvider( apiurl, config ) {
	config = config || {};

	this.setAPIurl( apiurl );
	this.fetchLimit = config.fetchLimit || 30;
	this.lang = config.lang;
	this.offset = config.offset || 0;
	this.ajaxSettings = config.ajaxSettings || {};

	this.staticParams = config.staticParams || {};
	this.userParams = config.userParams || {};

	this.toggleDepleted( false );

	// Mixin constructors
	OO.EventEmitter.call( this );
};

/* Setup */
OO.mixinClass( mw.flow.dm.APIResultsProvider, OO.EventEmitter );

/* Methods */

/**
 * Get results from the source
 *
 * @param {number} howMany Number of results to ask for
 * @return {jQuery.Promise} Promise that is resolved into an array
 * of available results, or is rejected if no results are available.
 */
mw.flow.dm.APIResultsProvider.prototype.getResults = function ( howMany ) {
	var xhr,
		aborted = false,
		provider = this;

	return this.fetchAPIresults( howMany )
		.then(
			function ( results ) {
				if ( !results || results.length === 0 ) {
					provider.toggleDepleted( true );
					return [];
				}
				return results;
			},
			// Process failed, return an empty promise
			function () {
				provider.toggleDepleted( true );
				return $.Deferred().resolve( [] );
			}
		)
		.promise( { abort: function () {
			aborted = true;
			if ( xhr ) {
				xhr.abort();
			}
		} } );
};

/**
 * Call the API for search results.
 *
 * @abstract
 * @param {number} [howMany] The number of results to retrieve
 * @return {jQuery.Promise} Promise that resolves with an array of objects that contain
 *  the fetched data.
 */
mw.flow.dm.APIResultsProvider.prototype.fetchAPIresults = function ( howMany ) {
	return $.Deferred().resolve().promise( { abort: $.noop } );
};

/**
 * Set API url
 *
 * @param {string} apiurl API url
 */
mw.flow.dm.APIResultsProvider.prototype.setAPIurl = function ( apiurl ) {
	this.apiurl = apiurl;
};

/**
 * Set api url
 *
 * @returns {string} API url
 */
mw.flow.dm.APIResultsProvider.prototype.getAPIurl = function () {
	return this.apiurl;
};

/**
 * Get the static, non-changing data parameters sent to the API
 *
 * @returns {Object} Data parameters
 */
mw.flow.dm.APIResultsProvider.prototype.getStaticParams = function () {
	return this.staticParams;
};

/**
 * Get the user-inputted dybamic data parameters sent to the API
 *
 * @returns {Object} Data parameters
 */
mw.flow.dm.APIResultsProvider.prototype.getUserParams = function () {
	return this.userParams;
};

/**
 * Set the data parameters sent to the API
 *
 * @param {Object} params User defined data parameters
 */
mw.flow.dm.APIResultsProvider.prototype.setUserParams = function ( params ) {
	// Assymetrically compare (params is subset of this.userParams)
	if ( !ve.compare( params, this.userParams, true ) ) {
		this.userParams = $.extend( {}, this.userParams, params );
		// Reset offset
		this.setOffset( 0 );
		// Reset depleted status
		this.toggleDepleted( false );
	}
};

/**
 * Get fetch limit or 'page' size. This is the number
 * of results per request.
 *
 * @returns {number} limit
 */
mw.flow.dm.APIResultsProvider.prototype.getDefaultFetchLimit = function () {
	return this.limit;
};

/**
 * Set limit
 *
 * @param {number} limit Default number of results to fetch from the API
 */
mw.flow.dm.APIResultsProvider.prototype.setDefaultFetchLimit = function ( limit ) {
	this.limit = limit;
};

/**
 * Get provider API language
 *
 * @returns {string} Provider API language
 */
mw.flow.dm.APIResultsProvider.prototype.getLang = function () {
	return this.lang;
};

/**
 * Set provider API language
 *
 * @param {string} lang Provider API language
 */
mw.flow.dm.APIResultsProvider.prototype.setLang = function ( lang ) {
	this.lang = lang;
};

/**
 * Get result offset
 *
 * @returns {number} Offset Results offset for the upcoming request
 */
mw.flow.dm.APIResultsProvider.prototype.getOffset = function () {
	return this.offset;
};

/**
 * Set result offset
 *
 * @param {number} Results offset for the upcoming request
 */
mw.flow.dm.APIResultsProvider.prototype.setOffset = function ( offset ) {
	this.offset = offset;
};

/**
 * Check whether the provider is depleted and has no more results
 * to hand off.
 *
 * @returns {boolean} The provider is depleted
 */
mw.flow.dm.APIResultsProvider.prototype.isDepleted = function () {
	return this.depleted;
};

/**
 * Toggle depleted state
 *
 * @param {boolean} isDepleted The provider is depleted
 */
mw.flow.dm.APIResultsProvider.prototype.toggleDepleted = function ( isDepleted ) {
	this.depleted = isDepleted !== undefined ? isDepleted : !this.depleted;
};

/**
 * Get the default ajax settings
 *
 * @returns {Object} Ajax settings
 */
mw.flow.dm.APIResultsProvider.prototype.getAjaxSettings = function () {
	return this.ajaxSettings;
};

/**
 * Get the default ajax settings
 *
 * @param {Object} settings Ajax settings
 */
mw.flow.dm.APIResultsProvider.prototype.setAjaxSettings = function ( settings ) {
	this.ajaxSettings = settings;
};
