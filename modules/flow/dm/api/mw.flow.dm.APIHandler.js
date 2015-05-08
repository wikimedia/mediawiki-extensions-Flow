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
	 * @param {[type]} requestParams [description]
	 * @return {[type]} [description]
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
	 * @param {[type]} page [description]
	 * @param {[type]} offsetId [description]
	 * @return {[type]} [description]
	 */
	mw.flow.dm.APIHandler.prototype.getTopicList = function ( offsetId ) {
		var params = {
			page: this.page
		};

		if ( offsetId ) {
			params.vtloffsetId = offsetId;
		}

		return this.get(  'view-topiclist', params )
			.then( function ( data ) {
				return data.topiclist;
			} );
	};
}( jQuery ) );
