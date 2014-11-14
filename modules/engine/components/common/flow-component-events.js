/*!
 * Contains the code which registers and handles event callbacks.
 * In addition, it contains some common callbacks (eg. apiRequest)
 * @todo Find better places for a lot of the callbacks that have been placed here
 */

( function ( $, mw ) {
	var _isGlobalBound;

	/**
	 * This implements functionality for being able to capture the return value from a called event.
	 * In addition, this handles Flow event triggering and binding.
	 * @extends oo.EventEmitter
	 * @constructor
	 */
	function FlowComponentEventsMixin( $container ) {
		/**
		 * Stores event callbacks.
		 */
		this.UI = {
			events: {
				globalApiPreHandlers: {},
				apiPreHandlers: {},
				apiHandlers: {},
				interactiveHandlers: {},
				loadHandlers: {}
			}
		};

		// Init EventEmitter
		OO.EventEmitter.call( this );

		// Bind events to this instance
		this.bindComponentHandlers( FlowComponentEventsMixin.eventHandlers );

		// Bind element handlers
		this.bindNodeHandlers( FlowComponentEventsMixin.UI.events );

		// Container handlers
		// @todo move some to FlowBoardComponent events, rename the others to FlowComponent
		$container
			.off( '.FlowBoardComponent' )
			.on(
				'click.FlowBoardComponent keypress.FlowBoardComponent',
				'a, input, button, .flow-click-interactive',
				_getDispatchCallback( this, 'interactiveHandler' )
			)
			.on(
				'focus.FlowBoardComponent',
				'a, input, button, .flow-click-interactive',
				_getDispatchCallback( this, 'interactiveHandlerFocus' )
			)
			.on(
				'click.FlowBoardComponent focusin.FlowBoardComponent focusout.FlowBoardComponent',
				'.flow-menu',
				_getDispatchCallback( this, 'toggleHoverMenu' )
			)
			.on(
				'focusin.FlowBoardComponent',
				'input.mw-ui-input, textarea',
				_getDispatchCallback( this, 'focusField' )
			);

		if ( _isGlobalBound ) {
			// Don't bind window.scroll again.
			return;
		}
		_isGlobalBound = true;

		// Handle scroll events globally
		$( window )
			.on(
				'scroll.flow',
				$.throttle( 50, _getDispatchCallback( this, 'scroll' ) )
			);
	}
	OO.mixinClass( FlowComponentEventsMixin, OO.EventEmitter );

	FlowComponentEventsMixin.eventHandlers = {};
	FlowComponentEventsMixin.UI = {
		events: {
			interactiveHandlers: {}
		}
	};

	//
	// Prototype methods
	//

	/**
	 * Same as OO.EventEmitter.emit, except that it returns an array of results.
	 * If something returns false (or an object with _abort:true), we stop processing the rest of the callbacks, if any.
	 * @param {String} event Name of the event to trigger
	 * @param {...*} [args] Arguments to pass to event callback
	 * @returns {Array}
	 */
	function emitWithReturn( event, args ) {
		var i, len, binding, bindings, method,
			returns = [], retVal;

		if ( event in this.bindings ) {
			// Slicing ensures that we don't get tripped up by event handlers that add/remove bindings
			bindings = this.bindings[event].slice();
			args = Array.prototype.slice.call( arguments, 1 );
			for ( i = 0, len = bindings.length; i < len; i++ ) {
				binding = bindings[i];

				if ( typeof binding.method === 'string' ) {
					// Lookup method by name (late binding)
					method = binding.context[ binding.method ];
				} else {
					method = binding.method;
				}

				// Call function
				retVal = method.apply(
					binding.context,
					binding.args ? binding.args.concat( args ) : args
				);

				// Add this result to our list of return vals
				returns.push( retVal );

				if ( retVal === false || ( retVal && retVal._abort === true ) ) {
					// Returned false; stop running callbacks
					break;
				}
			}
			return returns;
		}
		return [];
	}
	FlowComponentEventsMixin.prototype.emitWithReturn = emitWithReturn;

	/**
	 *
	 * @param {Object} handlers
	 */
	function bindFlowComponentHandlers( handlers ) {
		var self = this;

		// Bind class event handlers, triggered by .emit
		$.each( handlers, function ( key, fn ) {
			self.on( key, function () {
				// Trigger callback with class instance context
				try {
					return fn.apply( self, arguments );
				} catch ( e ) {
					mw.flow.debug( 'Error in component handler:', key, e, arguments );
					return false;
				}
			} );
		} );
	}
	FlowComponentEventsMixin.prototype.bindComponentHandlers = bindFlowComponentHandlers;

	/**
	 * handlers can have keys globalApiPreHandlers, apiPreHandlers, apiHandlers, interactiveHandlers, loadHandlers
	 * @param {Object} handlers
	 */
	function bindFlowNodeHandlers( handlers ) {
		var self = this;

		// eg. { interactiveHandlers: { foo: Function } }
		$.each( handlers, function ( type, callbacks ) {
			// eg. { foo: Function }
			$.each( callbacks, function ( name, fn ) {
				// First time for this callback name, instantiate the callback list
				if ( !self.UI.events[type][name] ) {
					self.UI.events[type][name] = [];
				}
				if ( $.isArray( fn ) ) {
					// eg. UI.events.interactiveHandlers.foo concat [Function, Function];
					self.UI.events[type][name] = self.UI.events[type][name].concat( fn );
				} else {
					// eg. UI.events.interactiveHandlers.foo = [Function];
					self.UI.events[type][name].push( fn );
				}
			} );
		} );
	}
	FlowComponentEventsMixin.prototype.bindNodeHandlers = bindFlowNodeHandlers;

	//
	// Static methods
	//

	/**
	 * Utility to get error message for API result.
	 *
	 * @param string code
	 * @param Object result
	 * @returns string
	 */
	function flowGetApiErrorMessage( code, result ) {
		if ( result.error && result.error.info ) {
			return result.error.info;
		} else {
			if ( code === 'http' ) {
				// XXX: some network errors have English info in result.exception and result.textStatus.
				return mw.msg( 'flow-error-http' );
			} else {
				return mw.msg( 'flow-error-external', code );
			}
		}
	}
	FlowComponentEventsMixin.static.getApiErrorMessage = flowGetApiErrorMessage;

	//
	// Interactive Handlers
	//

	/**
	 * Triggers an API request based on URL and form data, and triggers the callbacks based on flow-api-handler.
	 * @example <a data-flow-interactive-handler="apiRequest" data-flow-api-handler="loadMore" data-flow-api-target="< .flow-component div" href="...">...</a>
	 * @param {Event} event
	 */
	function flowEventsMixinApiRequestInteractiveHandler( event ) {
		var $deferred,
			$target,
			preHandlerReturn,
			self = event.currentTarget || event.delegateTarget || event.target,
			$this = $( self ),
			flowComponent = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $this ),
			dataParams = $this.data(),
			handlerName = dataParams.flowApiHandler,
			preHandlerReturns = [],
			info = {
				$target: null,
				status: null
			},
			args = Array.prototype.slice.call( arguments, 0 );

		event.preventDefault();

		// Find the target node
		if ( dataParams.flowApiTarget ) {
			// This fn supports finding parents
			$target = $this.findWithParent( dataParams.flowApiTarget );
		}
		if ( !$target || !$target.length ) {
			// Assign a target node if none
			$target = $this;
		}
		info.$target = $target;
		args.splice( 1, 0, info ); // insert info into args for prehandler

		// Make sure an API call is not already in progress for this target
		if ( $target.closest( '.flow-api-inprogress' ).length ) {
			flowComponent.debug( 'apiRequest already in progress', arguments );
			return;
		}

		// Mark the target node as "in progress" to disallow any further API calls until it finishes
		$target.addClass( 'flow-api-inprogress' );
		$this.addClass( 'flow-api-inprogress' );

		// Let generic pre-handler take care of edit conflicts
		$.each( flowComponent.UI.events.globalApiPreHandlers, function( key, callbackArray ) {
			$.each( callbackArray, function ( i, callbackFn ) {
				preHandlerReturns.push( callbackFn.apply( self, args ) );
			} );
		} );

		// Use the pre-callback to find out if we should process this
		if ( flowComponent.UI.events.apiPreHandlers[ handlerName ] ) {
			// apiPreHandlers can return FALSE to prevent processing,
			// nothing at all to proceed,
			// or OBJECT to add param overrides to the API
			// or FUNCTION to modify API params
			$.each( flowComponent.UI.events.apiPreHandlers[ handlerName ], function ( i, callbackFn ) {
				preHandlerReturn = callbackFn.apply( self, args );
				preHandlerReturns.push( preHandlerReturn );

				if ( preHandlerReturn === false || preHandlerReturn._abort === true ) {
					// Callback returned false; break out of this loop
					return false;
				}
			} );

			if ( preHandlerReturn === false || preHandlerReturn._abort === true ) {
				// Last callback returned false
				flowComponent.debug( false, 'apiPreHandler returned false', handlerName, args );

				// Abort any old request in flight; this is normally done automatically by requestFromNode
				flowComponent.Api.abortOldRequestFromNode( self, null, null, preHandlerReturns );

				// @todo support for multiple indicators on same target
				$target.removeClass( 'flow-api-inprogress' );
				$this.removeClass( 'flow-api-inprogress' );

				return;
			}
		}

		// Make the request
		$deferred = flowComponent.Api.requestFromNode( self, preHandlerReturns );
		if ( !$deferred ) {
			mw.flow.debug( '[FlowApi] [interactiveHandlers] apiRequest element is not anchor or form element' );
			$deferred = $.Deferred();
			$deferred.rejectWith( { error: { info: 'Not an anchor or form' } } );
		}

		// Remove the load indicator
		$deferred.always( function () {
			// @todo support for multiple indicators on same target
			$target.removeClass( 'flow-api-inprogress' );
			$this.removeClass( 'flow-api-inprogress' );
		} );

		// Remove existing errors from previous attempts
		flowComponent.emitWithReturn( 'removeError', $this );

		// If this has a special api handler, bind it to the callback.
		if ( flowComponent.UI.events.apiHandlers[ handlerName ] ) {
			$deferred
				.done( function () {
					var args = Array.prototype.slice.call( arguments, 0 );
					info.status = 'done';
					args.unshift( info );
					$.each( flowComponent.UI.events.apiHandlers[ handlerName ], function ( i, callbackFn ) {
						callbackFn.apply( self, args );
					} );
				} )
				.fail( function ( code, result ) {
					var errorMsg,
						args = Array.prototype.slice.call( arguments, 0 ),
						$form = $this.closest( 'form' );

					info.status = 'fail';
					args.unshift( info );

					/*
					 * In the event of edit conflicts, store the previous
					 * revision id so we can re-submit an edit against the
					 * current id later.
					 */
					if ( result.error && result.error.prev_revision ) {
						$form.data( 'flow-prev-revision', result.error.prev_revision.revision_id );
					}

					/*
					 * Generic error handling: displays error message in the
					 * nearest error container.
					 *
					 * Errors returned by MW/Flow should always be in the
					 * same format. If the request failed without a specific
					 * error message, just fall back to some default error.
					 */
					errorMsg = flowComponent.constructor.static.getApiErrorMessage( code, result );
					flowComponent.emitWithReturn( 'showError', $this, errorMsg );

					$.each( flowComponent.UI.events.apiHandlers[ handlerName ], function ( i, callbackFn ) {
						callbackFn.apply( self, args );
					} );
				} );
		}
	}
	FlowComponentEventsMixin.UI.events.interactiveHandlers.apiRequest = flowEventsMixinApiRequestInteractiveHandler;

	//
	// Event handler methods
	//

	/**
	 *
	 * @param {FlowComponent|jQuery} $container or entire FlowComponent
	 * @todo Perhaps use name="flow-load-handler" for performance in older browsers
	 */
	function flowMakeContentInteractiveCallback( $container ) {
		if ( !$container.jquery ) {
			$container = $container.$container;
		}

		// Get the FlowComponent
		var component = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $container );

		// Find all load-handlers and trigger them
		$container.find( '.flow-load-interactive' ).add( $container.filter( '.flow-load-interactive' ) ).each( function () {
			var $this = $( this ),
				handlerName = $this.data( 'flow-load-handler' );

			// If this has a special load handler, run it.
			component.emitWithReturn( 'loadHandler', handlerName, $this );
		} );

		// Find all the forms
		// @todo move this into a flow-load-handler
		$container.find( 'form' ).add( $container.filter( 'form' ) ).each( function () {
			var $this = $( this );

			// Trigger for flow-actions-disabler
			$this.find( 'input, textarea' ).trigger( 'keyup' );

			// Find this form's inputs
			$this.find( 'textarea' ).filter( '[data-flow-expandable]').each( function () {
				// Compress textarea if:
				// the textarea isn't already focused
				// and the textarea doesn't have text typed into it
				if ( !$( this ).is( ':focus' ) && this.value === this.defaultValue ) {
					component.emitWithReturn( 'compressTextarea', $( this ) );
				}
			} );

			component.emitWithReturn( 'hideForm', $this );
		} );
	}
	FlowComponentEventsMixin.eventHandlers.makeContentInteractive = flowMakeContentInteractiveCallback;

	/**
	 * Triggers load handlers.
	 */
	function flowLoadHandlerCallback( handlerName, args, context ) {
		args = $.isArray( args ) ? args : ( args ? [args] : [] );
		context = context || this;

		if ( this.UI.events.loadHandlers[handlerName] ) {
			$.each( this.UI.events.loadHandlers[handlerName], function ( i, fn ) {
				fn.apply( context, args );
			} );
		}
	}
	FlowComponentEventsMixin.eventHandlers.loadHandler = flowLoadHandlerCallback;

	/**
	 * Executes interactive handlers.
	 *
	 * @param {array} args
	 * @param {jQuery} $context
	 * @param {string} interactiveHandlerName
	 * @param {string} apiHandlerName
	 */
	function flowExecuteInteractiveHandler( args, $context, interactiveHandlerName, apiHandlerName ) {
		// Call any matching interactive handlers
		if ( this.UI.events.interactiveHandlers[interactiveHandlerName] ) {
			$.each( this.UI.events.interactiveHandlers[interactiveHandlerName], function ( i, fn ) {
				fn.apply( $context[0], args );
			} );
		} else if ( this.UI.events.apiHandlers[apiHandlerName] ) {
			// Call any matching API handlers
			$.each( this.UI.events.interactiveHandlers.apiRequest, function ( i, fn ) {
				fn.apply( $context[0], args );
			} );
		} else if ( interactiveHandlerName ) {
			this.debug( 'Failed to find interactiveHandler', interactiveHandlerName, arguments );
		} else if ( apiHandlerName ) {
			this.debug( 'Failed to find apiHandler', apiHandlerName, arguments );
		}
	}

	/**
	 * Triggers both API and interactive handlers, on click/enter.
	 */
	function flowInteractiveHandlerCallback( event ) {
		// Only trigger with enter key, if keypress
		if ( event.type === 'keypress' && ( event.charCode !== 13 || event.metaKey || event.shiftKey || event.ctrlKey || event.altKey )) {
			return;
		}

		var args = Array.prototype.slice.call( arguments, 0 ),
			$context = $( event.currentTarget || event.delegateTarget || event.target ),
			interactiveHandlerName = $context.data( 'flow-interactive-handler' ),
			apiHandlerName = $context.data( 'flow-api-handler' );

		return flowExecuteInteractiveHandler.call( this, args, $context, interactiveHandlerName, apiHandlerName );
	}
	FlowComponentEventsMixin.eventHandlers.interactiveHandler = flowInteractiveHandlerCallback;
	FlowComponentEventsMixin.eventHandlers.apiRequest = flowInteractiveHandlerCallback;

	/**
	 * Triggers both API and interactive handlers, on focus.
	 */
	function flowInteractiveHandlerFocusCallback( event ) {
		var args = Array.prototype.slice.call( arguments, 0 ),
			$context = $( event.currentTarget || event.delegateTarget || event.target ),
			interactiveHandlerName = $context.data( 'flow-interactive-handler-focus' ),
			apiHandlerName = $context.data( 'flow-api-handler-focus' );

		return flowExecuteInteractiveHandler.call( this, args, $context, interactiveHandlerName, apiHandlerName );
	}
	FlowComponentEventsMixin.eventHandlers.interactiveHandlerFocus = flowInteractiveHandlerFocusCallback;

	/**
	 * When the whole class has been instantiated fully (after every constructor has been called).
	 * @param {FlowComponent} component
	 */
	function flowEventsMixinInstantiationComplete( component ) {
		$( window ).trigger( 'scroll.flow' );
	}
	FlowComponentEventsMixin.eventHandlers.instantiationComplete = flowEventsMixinInstantiationComplete;


	/**
	 * Compress and hide a flow form and/or its actions, depending on data-flow-initial-state.
	 * @param {jQuery} $form
	 * @todo Move this to a separate file
	 */
	function flowEventsMixinHideForm( $form ) {
		var initialState = $form.data( 'flow-initial-state' ),
			component = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $form );

		// Store state
		$form.data( 'flow-state', 'hidden' );

		// If any preview is visible cancel it
		// Must be done before compressing text areas because
		// the preview may have manipulated them.
		if ( $form.parent().find( '.flow-preview-warning' ).length ) {
			component.resetPreview(
				$form.find( 'button[data-role="cancel"]' )
			);
		}

		$form.find( 'textarea' ).each( function () {
			var $editor = $( this );

			// Kill editor instances
			if ( mw.flow.editor && mw.flow.editor.exists( $editor ) ) {
				mw.flow.editor.destroy( $editor );
			}

			// Drop the new input in place if:
			// the textarea isn't already focused
			// and the textarea doesn't have text typed into it
			if ( !$editor.is( ':focus' ) && this.value === this.defaultValue ) {
				component.emitWithReturn( 'compressTextarea', $editor );
			}
		} );

		if ( initialState === 'collapsed' ) {
			// Hide its actions
			// @todo Use TemplateEngine to find and hide actions?
			$form.find( '.flow-form-collapsible' ).hide();
			$form.data( 'flow-form-collapse-state', 'collapsed' );
		} else if ( initialState === 'hidden' ) {
			// Hide the form itself
			$form.hide();
		}
	}
	FlowComponentEventsMixin.eventHandlers.hideForm = flowEventsMixinHideForm;

	/**
	 * "Compresses" a textarea by adding a class to it, which CSS will pick up
	 * to force a smaller display size.
	 * @param {jQuery} $textarea
	 * @todo Move this to a separate file
	 */
	function flowEventsMixinCompressTextarea( $textarea ) {
		$textarea.addClass( 'flow-input-compressed' );
	}
	FlowComponentEventsMixin.eventHandlers.compressTextarea = flowEventsMixinCompressTextarea;

	/**
	 * If input is focused, expand it if compressed (into textarea).
	 * Otherwise, trigger the form to unhide.
	 * @param {Event} event
	 * @todo Move this to a separate file
	 */
	function flowEventsMixinFocusField( event ) {
		var $context = $( event.currentTarget || event.delegateTarget || event.target ),
			component = mw.flow.getPrototypeMethod( 'component', 'getInstanceByElement' )( $context );

		// Expand this textarea
		component.emitWithReturn( 'expandTextarea', $context );

		// Show the form (and swap it for textarea if needed)
		component.emitWithReturn( 'showForm', $context.closest( 'form' ) );
	}
	FlowComponentEventsMixin.eventHandlers.focusField = flowEventsMixinFocusField;

	/**
	 * Expand and make visible a flow form and/or its actions, depending on data-flow-initial-state.
	 * @param {jQuery} $form
	 */
	function flowEventsMixinShowForm( $form ) {
		var initialState = $form.data( 'flow-initial-state' ),
			self = this;

		if ( initialState === 'collapsed' ) {
			// Show its actions
			if ( $form.data( 'flow-form-collapse-state' ) === 'collapsed' ) {
				$form.removeData( 'flow-form-collapse-state' );
				$form.find( '.flow-form-collapsible' ).show();
			}
		} else if ( initialState === 'hidden' ) {
			// Show the form itself
			$form.show();
		}

		// Expand all textareas if needed
		$form.find( '.flow-input-compressed' ).each( function () {
			self.emitWithReturn( 'expandTextarea', $( this ) );
		} );

		// Initialize editors, turning them from textareas into editor objects
		self.emitWithReturn( 'initializeEditors', $form );

		// Store state
		$form.data( 'flow-state', 'visible' );
	}
	FlowComponentEventsMixin.eventHandlers.showForm = flowEventsMixinShowForm;

	/**
	 * Expand the textarea by removing the CSS class that will make it appear
	 * smaller.
	 * @param {jQuery} $textarea
	 */
	function flowEventsMixinexpandTextarea( $textarea ) {
		$textarea.removeClass( 'flow-input-compressed' );
	}
	FlowComponentEventsMixin.eventHandlers.expandTextarea = flowEventsMixinexpandTextarea;

	/**
	 * Initialize all editors, turning them from textareas into editor objects.
	 *
	 * @param {jQuery} $container
	 */
	function flowEventsMixinInitializeEditors( $container ) {
		var flowComponent = this;

		mw.loader.using( 'ext.flow.editor', function() {
			var $editors = $container.find( 'textarea' );

			$editors.each( function() {
				var $editor = $( this );

				// All editors already have their content in wikitext-format
				// (mostly because we need to prefill them server-side so that
				// JS-less users can interact)
				mw.flow.editor.load( $editor, $editor.val(), 'wikitext' );

				// Kill editor instance when the form it's in is cancelled
				flowComponent.emitWithReturn( 'addFormCancelCallback', $editor.closest( 'form' ), function() {
					if ( mw.flow.editor.exists( $editor ) ) {
						mw.flow.editor.destroy( $editor );
					}
				} );
			} );
		} );
	}
	FlowComponentEventsMixin.eventHandlers.initializeEditors = flowEventsMixinInitializeEditors;

	/**
	 * Adds a flow-cancel-callback to a given form, to be triggered on click of the "cancel" button.
	 * @param {jQuery} $form
	 * @param {Function} callback
	 */
	function flowEventsMixinAddFormCancelCallback( $form, callback ) {
		var fns = $form.data( 'flow-cancel-callback' ) || [];
		fns.push( callback );
		$form.data( 'flow-cancel-callback', fns );
	}
	FlowComponentEventsMixin.eventHandlers.addFormCancelCallback = flowEventsMixinAddFormCancelCallback;

	/**
	 * @param {FlowBoardComponent|jQuery} $node or entire FlowBoard
	 */
	function flowEventsMixinRemoveError( $node ) {
		_flowFindUpward( $node, '.flow-error-container' ).filter( ':first' ).empty();
	}
	FlowComponentEventsMixin.eventHandlers.removeError = flowEventsMixinRemoveError;

	/**
	 * @param {FlowBoardComponent|jQuery} $node or entire FlowBoard
	 * @param {String} msg The error that occurred. Currently hardcoded.
	 */
	function flowEventsMixinShowError( $node, msg ) {
		var html = mw.flow.TemplateEngine.processTemplate( 'flow_errors', { errors: [ { message: msg } ] } );

		if ( !$node.jquery ) {
			$node = $node.$container;
		}

		_flowFindUpward( $node, '.flow-content-preview' ).hide();
		_flowFindUpward( $node, '.flow-error-container' ).filter( ':first' ).replaceWith( html );
	}
	FlowComponentEventsMixin.eventHandlers.showError = flowEventsMixinShowError;

	/**
	 * On click, focus, and blur of hover menu events, decides whether or not to hide or show the expanded menu
	 * @param {Event} event
	 */
	function flowEventsMixinToggleHoverMenu( event ) {
		var $this = $( event.target ),
			$menu = $this.closest( '.flow-menu' );

		if ( event.type === 'click' ) {
			// If the caret was clicked, toggle focus
			if ( $this.closest( '.flow-menu-js-drop' ).length ) {
				$menu.toggleClass( 'focus' );

				// This trick lets us wait for a blur event from A instead on body, to later hide the menu on outside click
				if ( $menu.hasClass( 'focus' ) ) {
					$menu.find( '.flow-menu-js-drop' ).find( 'a' ).focus();
				}
			}
		} else if ( event.type === 'focusin' ) {
			// If we are focused on a menu item (eg. tabbed in), open the whole menu
			$menu.addClass( 'focus' );
		} else if ( event.type === 'focusout' && !$menu.find( 'a' ).filter( ':focus' ).length ) {
			// If we lost focus, make sure no other element in this menu has focus, and then hide the menu
			$menu.removeClass( 'focus' );
		}
	}
	FlowComponentEventsMixin.eventHandlers.toggleHoverMenu = flowEventsMixinToggleHoverMenu;

	/**
	 * Shows a tooltip telling the user that they have subscribed
	 * to this topic|board
	 * @param  {jQuery} $tooltipTarget Element to attach tooltip to.
	 * @param  {string} type           'topic' or 'board'
	 * @param  {string} dir            Direction to point the pointer. 'left' or 'up'
	 */
	function flowEventsMixinShowSubscribedTooltip( $tooltipTarget, type, dir ) {
		dir = dir || 'left';

		mw.tooltip.show(
			$tooltipTarget,
			// tooltipTarget will not always be part of a FlowBoardComponent
			$( mw.flow.TemplateEngine.processTemplateGetFragment(
					'flow_tooltip_subscribed',
					{
						unsubscribe: false,
						type: type,
						direction: dir,
						username: mw.user.getName()
					}
				)
			).children(),
			{
				tooltipPointing: dir
			}
		);

		// Hide after 5s
		setTimeout( function () {
			mw.tooltip.hide( $tooltipTarget );
		}, 5000 );
	}
	FlowComponentEventsMixin.eventHandlers.showSubscribedTooltip = flowEventsMixinShowSubscribedTooltip;

	/**
	 * If a topic is collapsed, expand it.
	 * @param  {Element|jQuery} topic The (single) topic element to show
	 */
	function flowEventsMixinExpandTopicIfNecessary( topic ) {
		var $topic = $( topic ),
			flowBoard = mw.flow.getPrototypeMethod( 'board', 'getInstanceByElement' )( $topic ),
			isFullView = flowBoard.$container.hasClass( 'flow-board-collapsed-full' ),
			isInverted = $topic.hasClass( 'flow-element-collapsed-invert' );

		// Either full view and inverted (invisible)
		// or compacted view and not inverted (invisible)
		if ( isFullView === isInverted ) {
			$topic.toggleClass( 'flow-element-collapsed-invert' );
		}
	}
	FlowComponentEventsMixin.eventHandlers.expandTopicIfNecessary = flowEventsMixinExpandTopicIfNecessary;

	/**
	 * If a form has a cancelForm handler, we clear the form and trigger it. This allows easy cleanup
	 * and triggering of form events after successful API calls.
	 * @param {Element|jQuery} formElement
	 */
	function flowEventsMixinCancelForm( formElement ) {
		var $form = $( formElement ),
			$button = $form.find( 'button, input, a' ).filter( '[data-flow-interactive-handler="cancelForm"]' );

		if ( $button.length ) {
			// Clear contents to not trigger the "are you sure you want to
			// discard your text" warning
			$form.find( 'textarea, :text' ).each( function() {
				$( this ).val( this.defaultValue );
			} );

			// Trigger a click on cancel to have it destroy the form the way it should
			$button.trigger( 'click' );
		}
	}
	FlowComponentEventsMixin.eventHandlers.cancelForm = flowEventsMixinCancelForm;

	//
	// Private functions
	//

	/**
	 * Returns a callback function which passes off arguments to the emitter.
	 * This only exists to clean up the FlowComponentEventsMixin constructor,
	 * by preventing it from having too many anonymous functions.
	 * @param {FlowComponent|FlowComponentEventsMixin} context
	 * @param {String} name
	 * @returns {Function}
	 * @private
	 */
	function _getDispatchCallback( context, name ) {
		return function () {
			var args = Array.prototype.slice.call( arguments, 0 );

			// Add event name as first arg of emit
			args.unshift( name );

			return context.emitWithReturn.apply( context, args );
		};
	}

	/**
	 * Given node & a selector, this will return the result closest to $node
	 * by first looking inside $node, then travelling up the DOM tree to
	 * locate the first result in a common ancestor.
	 *
	 * @param {jQuery} $node
	 * @param {String} selector
	 * @returns jQuery
	 */
	function _flowFindUpward( $node, selector ) {
		// first check if result can already be found inside $node
		var $result = $node.find( selector );

		// then keep looking up the tree until a result is found
		while ( $result.length === 0 && $node.length !== 0 ) {
			$node = $node.parent();
			$result = $node.children( selector );
		}

		return $result;
	}

	// Copy static and prototype from mixin to main class
	mw.flow.mixinComponent( 'component', FlowComponentEventsMixin );
}( jQuery, mediaWiki ) );
