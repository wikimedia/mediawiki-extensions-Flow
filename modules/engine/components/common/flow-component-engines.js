/*!
 * Initializes StorageEngine (Storer), TemplateEngine (Handlebars), and API (FlowAPI).
 */

( function ( $, mw, initStorer ) {
	/**
	 * Initializes Storer, Handlebars, and FlowAPI.
	 * @constructor
	 */
	function FlowComponentEnginesMixin() {}
	OO.initClass( FlowComponentEnginesMixin );

	/**
	 * Contains Storer.js's (fallback) storage engines.
	 * @type {{ cookieStorage: Storer.cookieStorage, memoryStorage: Storer.memoryStorage, sessionStorage: Storer.sessionStorage, localStorage: Storer.localStorage }}
	 */
	mw.flow.StorageEngine = FlowComponentEnginesMixin.static.StorageEngine = {};
	mw.flow.StorageEngine = FlowComponentEnginesMixin.static.StorageEngine = initStorer( function ( Storer ) {
		// Callback (for older IE browsers; sets userData in place of localStorage on DOMReady)
		mw.flow.StorageEngine.cookieStorage  = FlowComponentEnginesMixin.static.StorageEngine.cookieStorage  = Storer.cookieStorage;
		mw.flow.StorageEngine.memoryStorage  = FlowComponentEnginesMixin.static.StorageEngine.memoryStorage  = Storer.memoryStorage;
		mw.flow.StorageEngine.sessionStorage = FlowComponentEnginesMixin.static.StorageEngine.sessionStorage = Storer.sessionStorage;
		mw.flow.StorageEngine.localStorage   = FlowComponentEnginesMixin.static.StorageEngine.localStorage   = Storer.localStorage;
	}, { 'prefix': '_WMFLOW_' } );

	/**
	 * Contains the Flow templating engine translation class (in case we change templating engines).
	 * @type {FlowHandlebars}
	 */
	mw.flow.TemplateEngine = FlowComponentEnginesMixin.static.TemplateEngine = new mw.flow.FlowHandlebars( FlowComponentEnginesMixin.static.StorageEngine );

	/**
	 * Flow API singleton
	 * @type {FlowAPI}
	 */
	mw.flow.API = new mw.flow.FlowAPI( FlowComponentEnginesMixin.static.StorageEngine );

	// Copy static and prototype from mixin to main class
	mw.flow.mixinComponent( 'component', FlowComponentEnginesMixin );
}( jQuery, mediaWiki, mediaWiki.flow.vendor.initStorer ) );
