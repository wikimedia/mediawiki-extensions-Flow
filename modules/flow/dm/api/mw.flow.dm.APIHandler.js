( function ( $ ) {
	/**
	 * Resource Provider object.
	 *
	 * @class
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
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

		this.requestParams = $.extend( {
			action: 'flow'
		}, config.requestParams );
	};

	OO.initClass( mw.flow.dm.APIHandler );

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
}( jQuery ) );
