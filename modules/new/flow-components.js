/*!
 * Implements basic Flow UI JavaScript functionality.
 */

( function ( $, mw, initStorer ) {
	var _componentRegistry = {};

	window.mw = window.mw || {}; // mw-less testing
	mw.flow = mw.flow || {}; // create mw.flow globally

	/**
	 * Instantiate one or more new FlowComponents.
	 * Uses data-flow-component to find the right class, and returns that new instance.
	 * @param {jQuery} $container
	 * @returns {FlowComponent|bool}
	 * @constructor
	 */
	function initFlowComponent( $container ) {
		var a, i, componentName;

		if ( !$container || !$container.length ) {
			// No containers found
			mw.flow.debug( 'Will not instantiate: no $container.length', arguments );
			return false;
		} else if ( $container.length > 1 ) {
			// Too many elements; instantiate them all
			for ( a = [], i = $container.length; i--; ) {
				a.push(initFlowComponent( $( $container[ i ] ) ));
			}
			return a;
		}

		// Find out which component this is
		componentName = $container.data( 'flow-component' );
		if ( _componentRegistry[ componentName ] ) {
			// Run progressive enhancements if any are needed by this container
			mw.flow.TemplateEngine.processProgressiveEnhancement( $container );
			// Return the new instance of that FlowComponent
			return new _componentRegistry[ componentName ]( $container );
		}

		// Don't know what kind of component this is.
		mw.flow.debug( 'Unknown FlowComponent: ', componentName, arguments );
		return false;
	}
	mw.flow.initComponent = initFlowComponent;

	/**
	 * Registers a given FlowComponent into the component registry, and also extends the class with FlowComponent.
	 * @param {String} name
	 * @param {Function} constructor
	 */
	mw.flow.registerComponent = ( function () {
		return function ( name, constructorClass ) {
			// Inherit FlowComponent
			constructorClass.prototype = new FlowComponent;
			// Keep the original constructor class
			constructorClass.prototype.constructor = constructorClass;
			/** @lends FlowComponent.prototype */ // Super weird thing below for jsdoc purposes
			( { parent: ( constructorClass.prototype.parent = FlowComponent ) } ); // jshint ignore:line
			// Create the instance registry for this component
			constructorClass._instanceRegistry = [];
			constructorClass._instanceRegistryById = {};
			_componentRegistry[ name ] = constructorClass;
		};
	}() );

	/**
	 * Inherited base class. Stores the instance in the class's instance registry.
	 * @param {jQuery} [$container]
	 * @returns {FlowComponent|bool}
	 * @constructor
	 */
	function FlowComponent( $container ) {
		if ( !arguments.length ) {
			return this; // funky constructor inheritance
		}

		// Store the container for later use
		this.$container = $container;

		// Get this component's ID
		this.id = $container.data( 'flow-id' );
		if ( !this.id ) {
			// Generate an ID for this component
			this.id = 'flow-generated-' + FlowComponent._totalInstanceCount;
			$container.data( 'flow-id', this.id );
		} else if ( this.getInstanceByElement( $container ) ) {
			// Check if this board was already instantiated, and return that instead
			return this.getInstanceByElement( $container );
		}

		// Keep this in the registry to find it by other means
		this.constructor._instanceRegistryById[ this.id ] = this.constructor._instanceRegistry.push( this ) - 1;
		FlowComponent._totalInstanceCount++;
	}

	FlowComponent._totalInstanceCount = 0;

	/**
	 * Contains Storer.js's (fallback) storage engines.
	 * @type {{ cookieStorage: Storer.cookieStorage, memoryStorage: Storer.memoryStorage, sessionStorage: Storer.sessionStorage, localStorage: Storer.localStorage }}
	 */
	mw.flow.StorageEngine = FlowComponent.prototype.StorageEngine = {};
	mw.flow.StorageEngine = FlowComponent.prototype.StorageEngine = initStorer( function ( Storer ) {
		// Callback (for older IE browsers; sets userData in place of localStorage on DOMReady)
		mw.flow.StorageEngine.cookieStorage  = FlowComponent.prototype.StorageEngine.cookieStorage  = Storer.cookieStorage;
		mw.flow.StorageEngine.memoryStorage  = FlowComponent.prototype.StorageEngine.memoryStorage  = Storer.memoryStorage;
		mw.flow.StorageEngine.sessionStorage = FlowComponent.prototype.StorageEngine.sessionStorage = Storer.sessionStorage;
		mw.flow.StorageEngine.localStorage   = FlowComponent.prototype.StorageEngine.localStorage   = Storer.localStorage;
	}, { 'prefix': '_WMFLOW_' } );

	/**
	 * Contains the Flow templating engine translation class (in case we change templating engines).
	 * @type {FlowHandlebars}
	 */
	mw.flow.TemplateEngine = FlowComponent.prototype.TemplateEngine = new mw.flow.FlowHandlebars( FlowComponent.prototype.StorageEngine );

	/**
	 * Contains the Flow history state manager.
	 * @type {FlowHistoryStateManager}
	 */
	mw.flow.HistoryEngine = FlowComponent.prototype.HistoryEngine = new mw.flow.FlowHistoryStateManager( FlowComponent.prototype.StorageEngine );

	/**
	 * Takes any length of arguments, and passes it off to console.log.
	 * Only renders if window.flow_debug OR localStorage.flow_debug == true OR user is Admin or (WMF).
	 * @param {...*} args
	 */
	mw.flow.debug = FlowComponent.prototype.debug = function ( args ) {
		if ( window.console && ( window.flow_debug || ( window.localStorage && localStorage.getItem( 'flow_debug' ) ) || ( window.mw && mw.user && mw.user.name().match( /(^Admin$)|\(WMF\)/ ) ) ) ) {
			args = Array.prototype.slice.apply( arguments );
			args.unshift( '[FLOW] ' );
			console.log.apply( console, args );
		}
	};

	/**
	 * Returns all the registered instances of a given FlowComponent.
	 * @returns {FlowComponent[]}
	 */
	FlowComponent.prototype.getInstances = function () {
		return this.constructor._instanceRegistry;
	};

	/**
	 * Goes up the DOM tree to find which FlowComponent $el belongs to, via .flow-component[flow-id].
	 * @param {jQuery} $el
	 * @returns {FlowComponent|bool}
	 */
	FlowComponent.prototype.getInstanceByElement = function ( $el ) {
		var $container = $el.closest( '.flow-component' ),
			id = $container.data( 'flow-id' );

		return this.constructor._instanceRegistry[ this.constructor._instanceRegistryById[ id ] ] || false;
	};
}( jQuery, mediaWiki, mediaWiki.flow.vendor.initStorer ) );
