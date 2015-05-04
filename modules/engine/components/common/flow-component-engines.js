/*!
 * Initializes StorageEngine (Storer), TemplateEngine (Handlebars), and API (FlowApi).
 */

( function ( $, mw, initStorer ) {
	/**
	 * Initializes Storer, Handlebars, and FlowApi.
	 * @constructor
	 */
	function FlowComponentEnginesMixin() {}
	OO.initClass( FlowComponentEnginesMixin );

	/**
	 * Contains Storer.js's (fallback) storage engines.
	 * @type {Object} { cookieStorage: Storer.cookieStorage, memoryStorage: Storer.memoryStorage, sessionStorage: Storer.sessionStorage, localStorage: Storer.localStorage }}
	 */
	mw.flow.StorageEngine = FlowComponentEnginesMixin.static.StorageEngine = initStorer( { prefix: '_WMFLOW_' } );

	/**
	 * Contains the Flow templating engine translation class (in case we change templating engines).
	 * @type {FlowHandlebars}
	 */
	mw.flow.TemplateEngine = FlowComponentEnginesMixin.static.TemplateEngine = new mw.flow.FlowHandlebars( FlowComponentEnginesMixin.static.StorageEngine );

	/**
	 * Flow API singleton
	 * @type {FlowApi}
	 */
	mw.flow.Api = new mw.flow.FlowApi( FlowComponentEnginesMixin.static.StorageEngine );

	// Copy static and prototype from mixin to main class
	mw.flow.mixinComponent( 'component', FlowComponentEnginesMixin );
}( jQuery, mediaWiki, mediaWiki.flow.vendor.initStorer ) );
