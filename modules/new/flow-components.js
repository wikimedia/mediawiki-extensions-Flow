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
	 * @extends EventEmitter
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

		// Give this board its own API instance
		this.API = new mw.flow.FlowAPI( FlowComponent.prototype.StorageEngine, this.id );

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
	 * Contains the Flow history state manager.
	 * @type {FlowHistoryStateManager}
	 */
	mw.flow.API = new mw.flow.FlowAPI( FlowComponent.prototype.StorageEngine );

	/**
	 * Takes any length of arguments, and passes it off to console.log.
	 * Only renders if window.flow_debug OR localStorage.flow_debug == true OR user is Admin or (WMF).
	 * @param {...*} args
	 */
	mw.flow.debug = FlowComponent.prototype.debug = function ( args ) {
		if ( window.console && (
				window.flow_debug ||
				( window.localStorage && localStorage.getItem( 'flow_debug' ) ) ||
				( mw.user && !mw.user.isAnon() && mw.user.getName().match( /(^Admin$)|\(WMF\)/ ) )
			) ) {
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
			id;

		// This element isn't _within_ any actual component; was it spawned _by_ a component?
		if ( !$container.length ) {
			// Find any parents of this element with the flowSpawnedBy data attribute
			$container = $el.parents().addBack().filter( function () {
				return $( this ).data( 'flowSpawnedBy' );
			} ).last()
				// Get the flowSpawnedBy node
				.data( 'flowSpawnedBy' );
			// and then return the closest flow-component of it
			$container = $container ? $container.closest( '.flow-component' ) : $();
		}

		// Still no parent component. Crap out!
		if ( !$container.length ) {
			mw.flow.debug( 'Failed to getInstanceByElement: no $container.length', arguments );
			return false;
		}

		id = $container.data( 'flow-id' );

		return this.constructor._instanceRegistry[ this.constructor._instanceRegistryById[ id ] ] || false;
	};

	/**
	 * Sets the FlowComponent's $container element as the data-flow-spawned-by attribute on $el.
	 * Fires ALL events from within $el onto $eventTarget, albeit with the whole event intact.
	 * This allows us to listen for events from outside of FlowComponent's nodes, but still trigger them within.
	 * @param {jQuery} $el
	 * @param {jQuery} [$eventTarget]
	 */
	FlowComponent.prototype.assignSpawnedNode = function ( $el, $eventTarget ) {
		// Target defaults to .flow-component
		$eventTarget = $eventTarget || this.$container;

		// Assign flowSpawnedBy data attribute
		$el.data( 'flowSpawnedBy', $eventTarget );

		// Forward all events (except mouse movement) to $eventTarget
		$el.on(
			'blur change click dblclick error focus focusin focusout hover keydown keypress keyup load mousedown mouseup resize scroll select submit',
			'*',
			{ flowSpawnedBy: this.$container, flowSpawnedFrom: $el },
			function ( event ) {
				// Let's forward these events in an unusual way, similar to how jQuery propagates events...
				// First, only take the very first, top-level event, as the rest of the propagation is handled elsewhere
				if ( event.target === this ) {
					// Get all the parent nodes of our target,
					// but do not include any nodes we will already be bubbling up to (eg. body)
					var $nodes = $eventTarget.parents().addBack().not( $( this ).parents().addBack() ),
						i = $nodes.length;

					// For every node between $eventTarget and window that was not filtered out above...
					while ( i-- ) {
						// Trigger a bubbling event on each one, with the correct context
						_eventForwardDispatch.call( $nodes[i], event, $el[0] );
					}
				}
			}
		);
	};

	/**
	 * This method is mostly cloned from jQuery.event.dispatch, except that it has been modified to use container
	 * as its base for finding event handlers (via jQuery.event.handlers). This allows us to trigger events on said
	 * container (and its parents, bubbling up), as if the event originated from within it.
	 * jQuery itself doesn't allow for this, as the context (this & event.currentTarget) become the actual element you
	 * are triggering an event on, instead of the element which matched the selector.
	 *
	 * @example _eventForwardDispatch.call( Element, Event, Element );
	 *
	 * @param {jQuery.Event} event
	 * @param {Element} container
	 * @returns {*}
	 * @private
	 */
	function _eventForwardDispatch( event, container ) {
		// Make a writable jQuery.Event from the native event object
		event = jQuery.event.fix( event );

		var i, ret, handleObj, matched, j,
			handlerQueue = [],
			args = Array.prototype.slice.call( arguments ),
			handlers = ( jQuery._data( this, "events" ) || {} )[ event.type ] || [],
			special = jQuery.event.special[ event.type ] || {};

		// Use the fix-ed jQuery.Event rather than the (read-only) native event
		args[0] = event;
		event.delegateTarget = this;

		// Call the preDispatch hook for the mapped type, and let it bail if desired
		if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
			return;
		}

		// Determine handlers
		// The important modification: we use container instead of this as the context
		handlerQueue = jQuery.event.handlers.call( container, event, handlers );

		// Run delegates first; they may want to stop propagation beneath us
		i = 0;
		while ( (matched = handlerQueue[ i++ ]) && !event.isPropagationStopped() ) {
			event.currentTarget = matched.elem;

			j = 0;
			while ( (handleObj = matched.handlers[ j++ ]) && !event.isImmediatePropagationStopped() ) {
				// Triggered event must either 1) have no namespace, or
				// 2) have namespace(s) a subset or equal to those in the bound event (both can have no namespace).
				if ( !event.namespace_re || event.namespace_re.test( handleObj.namespace ) ) {

					event.handleObj = handleObj;
					event.data = handleObj.data;

					ret = ( (jQuery.event.special[ handleObj.origType ] || {}).handle || handleObj.handler )
						.apply( matched.elem, args );

					if ( ret !== undefined ) {
						if ( (event.result = ret) === false ) {
							event.preventDefault();
							event.stopPropagation();
						}
					}
				}
			}
		}

		// Call the postDispatch hook for the mapped type
		if ( special.postDispatch ) {
			special.postDispatch.call( this, event );
		}

		return event.result;
	}

}( jQuery, mediaWiki, mediaWiki.flow.vendor.initStorer ) );
