( function ( $ ) {
	/**
	 * Resource Provider object.
	 *
	 * @class
	 *
	 * @constructor
	 * @param {string} page Full page name with its namespace;
	 *  for example: "User_talk:Foo"
	 * @param {Object} [config] Configuration options
	 * @cfg {Object} [currentRevision] Current revision Id. Mostly used
	 *  for edit conflict check.
	 * @cfg {Object} [apiConstructorParams] Parameters for mw.Api()
	 * @cfg {Object} [requestParams] Parameters for the request
	 */
	mw.flow.dm.APIHandler = function FlowDmAPIHandler( page, config ) {
		config = config || {};

		this.apiConstructorParams = $.extend( {
			ajax: {
				timeout: 5 * 1000, // 5 seconds
				cache: false
			}
		}, config.apiConstructorParams );

		this.page = page;
		this.setCurrentRevision( config.currentRevision );

		this.requestParams = $.extend( {
			action: 'flow'
		}, config.requestParams );
	};

	OO.initClass( mw.flow.dm.APIHandler );

	/**
	 * Set the current revision Id. this is mostly used for edit actions, to check
	 * for edit conflicts.
	 *
	 * @param {string} revisionId Current revision id
	 */
	mw.flow.dm.APIHandler.prototype.setCurrentRevision = function ( revisionId ) {
		this.currentRevision = revisionId;
	};

	/**
	 * General get request
	 * @param {string} submodule The requested submodule
	 * @param {Object} requestParams API request parameters
	 * @return {jQuery.Promise} Promise that is resolved when the API request
	 *  is done, with the API result.
	 */
	mw.flow.dm.APIHandler.prototype.get = function ( submodule, requestParams ) {
		var params = $.extend( { submodule: submodule }, this.requestParams, requestParams );

		return ( new mw.Api() ).get( params )
			.then( function ( data ) {
				return data.flow[ submodule ].result;
			} );
	};

	/**
	 * Send a request to get topic list
	 *
	 * @param {string} orderType Sort order type, 'newest' or 'updated'
	 * @cfg {string} [offset] Topic offset id or timestamp offset
	 *  if given, the topic list will be returned with topics that
	 *  are after (and including) the topic with the given uuid or
	 *  after the given timestamp.
	 * @cfg {string} [toconly] Receive a stripped reply that fits the ToC. For more information
	 *  see 'toconly' in the API documentation.
	 * @return {jQuery.Promise} Promise that is resolved with the topiclist response
	 */
	mw.flow.dm.APIHandler.prototype.getTopicList = function ( orderType, config ) {
		var params = {
			page: this.page
		};

		config = config || {};

		params.vtltoconly = !!config.toconly;
		params.vtllimit = config.toconly ? 50 : 10;

		if ( orderType === 'newest' ) {
			params[ 'vtloffset-id' ] = config.offset;
		} else if ( orderType === 'updated' ) {
			// Translate api/object-given offset to MW offset for the API request
			params.vtloffset = moment.utc( config.offset ).format( 'YYYYMMDDHHmmss' );
		}

		return this.get( 'view-topiclist', params )
			.then( function ( data ) {
				return data.topiclist;
			} );
	};

	/**
	 * Get the board description from the API.
	 *
	 * @param {string} [contentFormat='fixed-html'] Content format for board description
	 * @return {jQuery.Promise} Promise that is resolved with the header response
	 */
	mw.flow.dm.APIHandler.prototype.getDescription = function ( contentFormat ) {
		var params = {
			page: this.page,
			vhformat: contentFormat || 'fixed-html'
		};

		return this.get( 'view-header', params )
			.then( function ( data ) {
				return data.header;
			} );
	};

	/**
	 * Save header information.
	 *
	 * @param {string} content Header content
	 * @param {string} [format='fixed-html'] Content format for board description
	 * @return {jQuery.Promise} Promise that is resolved with the save header response
	 */
	mw.flow.dm.APIHandler.prototype.saveDescription = function ( content, format, config ) {
		var params = {
				action: 'flow',
				page: this.page,
				submodule: 'edit-header',
				ehcontent: content,
				ehformat: format,
				ehprev_revision: this.currentRevision
			};

		return ( new mw.Api() ).postWithToken( 'edit', params )
			.then( function ( data ) {
				return OO.getProp( data.flow, 'edit-header', 'committed', 'header', 'header-revision-id' );
			} );
	};

}( jQuery ) );
