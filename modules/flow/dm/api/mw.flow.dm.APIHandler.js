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
}( jQuery ) );
