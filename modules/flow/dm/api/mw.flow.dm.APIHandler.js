( function ( $ ) {
	/**
	 * Resource Provider object.
	 *
	 * @class
	 * @mixins OO.EventEmitter
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {Object} [apiConstructorParams] Parameters for mw.Api()
	 * @cfg {Object} [requestParams] Parameters for the request
	 */
	mw.flow.dm.APIHandler = function FlowDmAPIHandler( page, config ) {
		config = config || {};

		// Mixin constructors
		OO.EventEmitter.call( this );

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

	/**
	 * General get request
	 * @param {string} submodule The requested submodule
	 * @param {Object} requestParams API request parameters
	 * @return {jQuery.Promise} Promise that is resolved when the API request
	 *  is done, with the API result.
	 */
	mw.flow.dm.APIHandler.prototype.get = function ( submodule, requestParams ) {
		var params = $.extend( this.requestParams, requestParams );

		return ( new mw.Api() ).get( $.extend( { submodule: submodule }, params ) )
			.then( function ( data ) {
				return data.flow[ submodule ].result;
			} );
	};

	/**
	 * Send a request to get topic list
	 *
	 * @param {string} sortType Sort type, 'newest' or 'updated'
	 * @param {string} [offset] Topic offset id or timestamp offset
	 *  if given, the topic list will be returned with topics that
	 *  are after (and including) the topic with the given uuid or
	 *  after the given timestamp.
	 * @return {jQuery.Promise} Promise that is resolved with the topiclist response
	 */
	mw.flow.dm.APIHandler.prototype.getTopicList = function ( orderType, offset, toconly ) {
		var params = {
			page: this.page
		};

		params.vtltoconly = !!toconly;
		params.vtllimit = toconly ? 50 : 10;

		if ( orderType === 'updated' ) {
			params['vtloffset-id'] = offset;
		} else {
			params.vtloffset = offset;
		}

		return this.get(  'view-topiclist', params )
			.then( function ( data ) {
				return data.topiclist;
			} );
	};
}( jQuery ) );
