/*!
 * Contains the FlowBoardComponent and related functionality.
 */

( function ( $, mw ) {
	/**
	 * Constructor class for instantiating a new Flow board. Returns a FlowBoardComponent object.
	 * Accepts one or more container elements in $container. If multiple, returns an array of FlowBoardComponents.
	 * @example <div class="flow-component" data-flow-component="board" data-flow-id="rqx495tvz888x5ur">...</div>
	 * @param {jQuery} $container
	 * @return {FlowBoardComponent|bool}
	 * @extends FlowComponent
	 * @constructor
	 */
	function FlowBoardComponent( $container ) {
		// Parent FlowComponent constructor
		var parentReturn = this.parent.apply( this, arguments );
		delete this.parent;
		if ( parentReturn && parentReturn.constructor ) {
			// If the parent returned an instantiated class (cached), return that
			return parentReturn;
		}

		// Default API submodule for FlowBoard URLs is to fetch a topiclist
		this.API.setDefaultSubmodule( 'topiclist-view' );

		// Set up the board
		if ( this.reinitializeBoard( $container ) === false ) {
			// Failed to init for some reason
			return false;
		}
	}

	// Register this FlowComponent
	mw.flow.registerComponent( 'board', FlowBoardComponent );

	/**
	 * Sets up the board and base properties on this class
	 * @return {Boolean}
	 */
	FlowBoardComponent.prototype.reinitializeBoard = function ( $container ) {
		// Instantiate this FlowBoardComponent
		// First, find our elements at the top level...
		var $header = $container.filter( '.flow-board-header' ),
			$boardNavigation = $container.filter( '.flow-board-navigation' ),
			$topicNavigation = $container.filter( '.flow-topic-navigation' ),
			$board = $container.filter( '.flow-board' );
		// ...then check at the second level...
		$header = $header.length ? $header : $container.find( '.flow-board-header' );
		$boardNavigation = $boardNavigation.length ? $boardNavigation : $container.find( '.flow-board-navigation' );
		$topicNavigation = $topicNavigation.length ? $topicNavigation : $container.find( '.flow-topic-navigation' );
		$board = $board.length ? $board : $container.find( '.flow-board' );

		// ...and remove any old ones that are in use.
		if ( $header.length ) {
			this.$header && this.$header.replaceWith( $header ) && this.$header.remove();
			this.$header = $header;
		}
		if ( $boardNavigation.length ) {
			this.$boardNavigation && this.$boardNavigation.replaceWith( $boardNavigation ) && this.$boardNavigation.remove();
			this.$boardNavigation = $boardNavigation;
		}
		if ( $board.length ) {
			this.$board && this.$board.replaceWith( $board ) && this.$board.remove();
			this.$board = $board;
		}
		if ( $topicNavigation.length ) {
			this.$topicNavigation && this.$topicNavigation.replaceWith( $topicNavigation ) && this.$topicNavigation.remove();
			this.$topicNavigation = $topicNavigation;
		}

		// Second, verify that this board in fact exists
		if ( !this.$board || !this.$board.length ) {
			// You need a board, dammit!
			this.debug( 'Could not find .flow-board', arguments );
			return false;
		}

		// Progressively enhance the board and its forms
		// @todo Needs a ~"liveUpdateComponents" method, since the functionality in makeContentInteractive needs to also run when we receive new content or update old content.
		// @todo move form stuff
		FlowBoardComponent.UI.makeContentInteractive( this );

		// Bind any necessary event handlers to this board
		FlowBoardComponent.UI.bindBoardHandlers( this );

		// Bind the global event handlers (only happens once per page load, on window/body)
		FlowBoardComponent.UI.bindGlobalHandlers();

		// Restore the last state
		this.HistoryEngine.restoreLastState();

		return true;
	};

	/**
	 * UI stuff
	 */
	FlowBoardComponent.UI = {
		/** Event handler callbacks */
		events: {
			/** Callbacks for data-flow-interactive-handler */
			interactiveHandlers: {},
			/** Callbacks for data-flow-load-handler */
			loadHandlers: {},
			/** Validity pre-callback for data-flow-api-handler */
			apiPreHandlers: {},
			/** Callbacks for data-flow-api-handler */
			apiHandlers: {}
		}
	};

	( function () {
		// Store out of global and FlowBoardComponent scope
		var _isGlobalBound = false;


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI pre-api callback handlers, to do things before the API call
		////////////////////

		/**
		 * Before a "Load More" button, verify that it isn't already in use and then disable it
		 * @param {Object} params
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.apiPreHandlers.loadMore = function ( params, event ) {
			var $this = $( this );

			if ( $this.is( ':disabled' ) ) {
				return false;
			}

			$this.prop( 'disabled', true );
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI api callback handlers
		////////////////////

		/**
		 * When a topic wrapper is generated or found on initial load...
		 * @param {String} status (done|fail)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.board = function ( status, data, jqxhr ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) ),
				$rendered;

			if ( status === 'done' ) {
				$rendered = $(
					flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_block_loop',
						{ blocks: data.flow[ 'topiclist-view' ].result }
					)
				).children();

				// Reinitialize the whole board with these nodes
				flowBoard.reinitializeBoard( $rendered );
			} else {
				// @todo fail
			}
		};

		/**
		 *
		 * @param {String} status (done|fail)
		 * @param {Object} data
		 * @param {jqXHR} jqxhr
		 */
		FlowBoardComponent.UI.events.apiHandlers.loadMore = function ( status, data, jqxhr ) {
			var $this = $( this ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $this ),
				$tmp;

			if ( status === 'done' ) {
				// Success
				// Render topiclist template
				$this.before(
					$tmp = $( flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_topiclist_loop',
						data.flow[ 'topiclist-view' ].result.topiclist
					) ).children()
				);
				// Run loadHandlers
				FlowBoardComponent.UI.makeContentInteractive( $tmp );

				// Render load more template
				$this.replaceWith(
					$tmp = $( flowBoard.TemplateEngine.processTemplateGetFragment(
						'flow_load_more',
						data.flow[ 'topiclist-view' ].result.topiclist
					) ).children()
				);

				// Run loadHandlers
				FlowBoardComponent.UI.makeContentInteractive( $tmp );

				// Remove the old load button (necessary if the above load_more template returns nothing)
				$this.remove();
			} else {
				// @todo fail
			}
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI on-element-load handlers
		////////////////////

		/**
		 * When a topic wrapper is generated or found on initial load...
		 * @param {jQuery} $topic
		 */
		FlowBoardComponent.UI.events.loadHandlers.topicElement = function ( $topic ) {
			// Get last collapse state from sessionStorage
			var states = mw.flow.StorageEngine.sessionStorage.getItem( 'topicCollapserStates' ) || {},
				topicId = $topic.data('flow-id');

			if ( states[ topicId ] ) {
				// This item was previously collapse-inverted, so reapply the class
				$topic.addClass( 'flow-topic-collapsed-invert' );
			}
		};

		/**
		 * Replaces $time with a new time element generated by TemplateEngine
		 * @param {jQuery} $time
		 */
		FlowBoardComponent.UI.events.loadHandlers.timestamp = function ( $time ) {
			$time.replaceWith(
				FlowBoardComponent.prototype.TemplateEngine.callHelper(
					'timestamp',
					parseInt( $time.attr( 'datetime' ), 10) * 1000,
					$time.data( 'time-str' ),
					$time.data( 'time-ago-only' ) === "1"
				)
			);
		};

		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI event interactive handlers
		////////////////////

		/**
		 * The activateForm handler will expand, scroll to, and then focus onto a form (target = field).
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.activateForm = function ( event ) {
			var $el, $form,
				href = $( this ).prop( 'href' ),
				hash = href.match( /#.+$/ ),
				$target = hash ? $( hash ) : false;

			// If this element is leading to another element on the page, find it.
			if ( $target && $target.length ) {
				$el = $( hash[0] );
				$form = $el.closest( 'form' );

				if ( $el.length && $form.length ) {
					// Is this a hidden form or invisible field? Make it visible.
					FlowBoardComponent.UI.Forms.showForm( $form );

					// Is this a form field? Scroll to the form instead of jumping.
					$form.conditionalScrollIntoView().queue( function ( next ) {
						var $el = $( hash[0] );

						// After scroll, focus onto the form field itself
						if ( $el.is( 'textarea, :text' ) ) {
							$el.focus();
						}

						// jQuery.dequeue
						next();
					});

					// OK, we're done here. Don't use the hard link.
					event.preventDefault();
				}
			}
		};

		/**
		 * Cancels and closes a form. If text has been entered, issues a warning first.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.cancelForm = function ( event ) {
			var $form = $( this ).closest( 'form' ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $form ),
				$fields = $form.find( 'textarea, :text' ),
				notEmptyCount = 0;

			event.preventDefault();

			// Check for non-empty fields of text
			$fields.each( function () {
				if ( $( this ).val() ) {
					notEmptyCount++;
					return false;
				}
			} );

			// If all the text fields are empty, OR if the user confirms to close this with text already entered, do it.
			if ( !notEmptyCount || confirm( flowBoard.TemplateEngine.l10n( 'You have entered text in this form. Are you sure you want to discard it?' ) ) ) {
				// Reset the form content
				$form[0].reset();

				// Trigger for flow-actions-disabler
				$form.find( 'textarea, :text' ).trigger( 'keyup' );

				// Hide the form
				FlowBoardComponent.UI.Forms.hideForm( $form );
			}

			if ( $form.data( 'flow-cancel-callback' ) ) {
				$form.data( 'flow-cancel-callback' )();
			}
		};

		/**
		 * @todo Implement with XHR. This is temporary for now.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.editContent = function ( event ) {
			if ( event.target !== this && $( event.target ).is( 'a, :input' )) {
				// Only run this for the main element, not clickable children
				return;
			}

			var $this = $( this ),
				html = $this.html();

			$this.hide();
			$this.before( '<form><textarea class="mw-ui-input"></textarea><div class="flow-form-actions"><button data-role="submit" class="mw-ui-button mw-ui-constructive">Save Changes</button><button data-flow-interactive-handler="cancelForm" data-role="cancel" class="mw-ui-button mw-ui-destructive mw-ui-sleeper">Discard</button></div></form>' );
			$this
				.prev( 'form' )
				.data( 'flow-cancel-callback', function () { $this.show(); $this.prev( 'form' ).remove(); } )
				.find( 'textarea' )
				.val( html )
				.focus();

			event.preventDefault();
		};

		/**
		 * Calls FlowBoardComponent.UI.collapserState to set and render the new Collapser state.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.collapserToggle = function ( event ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) );

			FlowBoardComponent.UI.collapserState( flowBoard, this.href.match( /[a-z]+$/ )[0] );

			event.preventDefault();
		};

		/**
		 * Toggles the flow-topic-expanded class on .flow-topic.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.topicCollapserToggle = function ( event ) {
			var $target = $( event.target ),
				$topic, topicId, states;

			// Make sure we didn't click on any interactive elements
			if ( $target.not( '.flow-menu-js-drop' ) && !$target.closest( 'a, button, input, textarea, select, ul, ol' ).length ) {
				// Toggle the class
				$topic = $( this ).closest( '.flow-topic' ).toggleClass( 'flow-topic-collapsed-invert' );
				topicId = $topic.data('flow-id');

				// Save in sessionStorage
				states = mw.flow.StorageEngine.sessionStorage.getItem( 'topicCollapserStates' ) || {};
				if ( $topic.hasClass( 'flow-topic-collapsed-invert' ) ) {
					states[ topicId ] = 1;
				} else {
					delete states[ topicId ];
				}
				mw.flow.StorageEngine.sessionStorage.setItem( 'topicCollapserStates', states );

				event.preventDefault();
				this.blur();
			}
		};

		/**
		 *
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.boardFilterMenuToggle = function ( event ) {
			var flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $( this ) );

			flowBoard.$boardNavigation.find( '.flow-board-filter-menu' )
				.find( '.flow-board-filter-menu-activator' ).click().end()
				.find( '.flow-ui-button-container' ).find( 'a:first' ).focus();

			event.preventDefault();
		};

		/**
		 * Triggers an API request based on URL and form data, and triggers the callbacks based on flow-api-handler.
		 * @example <a data-flow-interactive-handler="apiRequest" data-flow-api-handler="loadMore" href="...">...</a>
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.interactiveHandlers.apiRequest = function ( event ) {
			var $deferred,
				_this = this,
				$this = $( this ),
				flowBoard = FlowBoardComponent.prototype.getInstanceByElement( $this ),
				dataParams = $this.data(),
				handlerName = dataParams.flowApiHandler,
				$target;

			event.preventDefault();

			// Find the target node
			if ( dataParams.flowApiTarget ) {
				$target = flowBoard.find( dataParams.flowApiTarget );
			}
			if ( !$target || !$target.length ) {
				// Assign a target node if none
				$target = $( this );
			}

			// Make sure an API call is not already in progress for this target
			if ( $target.closest( '.flow-api-inprogress' ).length ) {
				this.debug( 'API call already in progress: ' + 'flow-api-query-temp-' + queryMap.action + '-' + queryMap.submodule, arguments );
				return;
			}

			// Mark the target node as "in progress" to disallow any further API calls until it finishes
			$target.addClass( 'flow-api-inprogress' );

			// Use the pre-callback to find out if we should process this
			if ( FlowBoardComponent.UI.events.apiPreHandlers[ handlerName ] ) {
				if ( FlowBoardComponent.UI.events.apiPreHandlers[ handlerName ].apply( _this, arguments ) === false ) {
					// Callback returned false
					return;
				}
			}

			// Make the request
			if ( $this.is( 'a' ) ) {
				$deferred = flowBoard.API.requestFromAnchor( this );
			} else if ( $this.is( 'input, button' ) ) {
				$deferred = flowBoard.API.requestFromForm( this );
			} else {
				mw.flow.debug( '[FlowAPI] [interactiveHandlers] apiRequest element is not anchor form' );
				$deferred = $.Deferred();
				$deferred.rejectWith( { error: 'Not an anchor or form' } );
			}

			// If this has a special api handler, bind it to the callback.
			if ( FlowBoardComponent.UI.events.apiHandlers[ handlerName ] ) {
				$deferred
					.done( function () {
						var args = Array.prototype.slice.call(arguments, 0);
						args.unshift( 'done' );
						FlowBoardComponent.UI.events.apiHandlers[ handlerName ].apply( _this, args );
					} )
					.fail( function () {
						var args = Array.prototype.slice.call(arguments, 0 );
						args.unshift( 'fail' );
						FlowBoardComponent.UI.events.apiHandlers[ handlerName ].apply( _this, args );
					} );
			}
		};

		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI events
		////////////////////

		/**
		 * On click of a, button, or input, we check to see if this is a link that has a special handler,
		 * defined through a data-flow-interactive-handler="name" attribute.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onClickInteractive = function ( event ) {
			// Only trigger with enter key
			if ( event.type === 'keypress' && ( event.charCode !== 13 || event.metaKey || event.shiftKey || event.ctrlKey || event.altKey )) {
				return;
			}

			var handlerName = $( this ).data( 'flow-interactive-handler' );

			// If this has a special click handler, run it.
			if ( FlowBoardComponent.UI.events.interactiveHandlers[ handlerName ] ) {
				FlowBoardComponent.UI.events.interactiveHandlers[ handlerName ].apply( this, arguments );
			}
		};

		/**
		 * If input is focused, expand it if compressed (into textarea).
		 * Otherwise, trigger the form to unhide.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onFocusField = function ( event ) {
			var $this = $( this );

			// Expand this input
			FlowBoardComponent.UI.Forms.expandInput( $this, event.target );

			// Show the form (and swap it for textarea if needed)
			FlowBoardComponent.UI.Forms.showForm( $this.closest( 'form' ) );
		};

		/**
		 * On click, focus, and blur of hover menu events, decides whether or not to hide or show the expanded menu
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onToggleHoverMenu = function ( event ) {
			var $this = $( event.target ),
				$menu = $this.closest( '.flow-menu' );

			if ( event.type === 'click' ) {
				// If the caret was clicked, toggle focus
				if ( $this.closest( '.flow-menu-js-drop' ).length ) {
					$menu.toggleClass( 'focus' );

					// This trick lets us wait for a blur event locally instead on body, to later hide the menu
					if ( $menu.hasClass( 'focus' ) ) {
						$menu.find( '.flow-menu-js-drop' ).find( 'a' ).focus();
					}
				}
			} else if ( event.type === 'focusin' ) {
				// If we are focused on a menu item, open the whole menu
				$menu.addClass( 'focus' );
			} else if ( event.type === 'focusout' && !$menu.find( 'a' ).filter( ':focus' ).length ) {
				// If we lost focus, make sure no other element in this menu has focus, and then hide the menu
				$menu.removeClass( 'focus' );
			}
		};

		/**
		 * Dispatches to other window.scroll events.
		 * @param {Event} event
		 */
		FlowBoardComponent.UI.events.onWindowScroll = function ( event ) {
			// The topic navigation sidebar
			FlowBoardComponent.UI.adjustTopicNavigationSidebar();

			// Infinite scroll handler
			FlowBoardComponent.UI.infiniteScrollCheck();
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI form methods
		////////////////////
		FlowBoardComponent.UI.Forms = {};

		/**
		 * If this input has a textarea stored on it, swap the elements in DOM.
		 * @param {jQuery} $input
		 * @param {Element} [target]
		 */
		FlowBoardComponent.UI.Forms.expandInput = function ( $input, target ) {
			var textarea = $.data( $input[0], 'flow-compressed' ),
				focused = $input.is( ':focus' );

			if ( textarea ) {
				// Swap the nodes
				$input.replaceWith( textarea );

				// Store this data again; jQuery has a habit of losing it after replaceWith
				$.data( textarea, 'flow-expanded', $input[0] );
				$.data( $input[0], 'flow-compressed', textarea );

				// target is a bug fix because the inputs are not being focused on click
				// @todo find out why this is happening ^
				if ( focused || $input[0] === target ) {
					// Swap focus!
					$( textarea ).focus()
						.closest( 'form' ).conditionalScrollIntoView();
				}
			}
		};

		/**
		 * If this textarea has an input stored on it, swap the elements in DOM.
		 * @param {jQuery} $textarea
		 */
		FlowBoardComponent.UI.Forms.compressTextarea = function ( $textarea ) {
			var input = $.data( $textarea[0], 'flow-expanded' );

			if ( input ) {
				// Swap the nodes
				$textarea.replaceWith( input );

				// Store this data again; jQuery has a habit of losing it after replaceWith
				$.data( $textarea[0], 'flow-expanded', input );
				$.data( input, 'flow-compressed', $textarea[0] );
			}
		};

		/**
		 * Compress and hide a flow form and/or its actions, depending on data-flow-initial-state.
		 * @param {jQuery} $form
		 */
		FlowBoardComponent.UI.Forms.hideForm = function ( $form ) {
			var initialState = $form.data( 'flow-initial-state' );

			// Store state
			$form.data( 'flow-state', 'hidden' );

			// Compress all textareas to inputs if needed
			$form.find( 'textarea' ).each( function () {
				FlowBoardComponent.UI.Forms.compressTextarea( $( this ) );
			} );

			if ( initialState === 'collapsed' ) {
				// Hide its actions
				// @todo Use TemplateEngine to find and hide actions?
				$form.find( '.flow-form-collapsible' ).hide();
			} else if ( initialState === 'hidden' ) {
				// Hide the form itself
				$form.hide();
			}
		};

		/**
		 * Expand and make visible a flow form and/or its actions, depending on data-flow-initial-state.
		 * @param {jQuery} $form
		 */
		FlowBoardComponent.UI.Forms.showForm = function ( $form ) {
			var initialState = $form.data( 'flow-initial-state' );

			if ( initialState === 'collapsed' ) {
				// Show its actions
				$form.find( '.flow-form-collapsible' ).show();
			} else if ( initialState === 'hidden' ) {
				// Show the form itself
				$form.show();
			}

			// Expand all inputs to textareas if needed
			$form.find( 'input' ).each( function () {
				FlowBoardComponent.UI.Forms.expandInput( $( this ) );
			} );

			// Store state
			$form.data( 'flow-state', 'visible' );
		};


		////////////////////////////////////////////////////////////
		// FlowBoardComponent.UI methods
		////////////////////

		/**
		 *
		 * @param {FlowBoardComponent|jQuery} $container or entire FlowBoard
		 * @todo Move to FlowComponent somehow, perhaps use name="flow-load-handler" for performance in older browsers
		 * @todo use EventEmitter to handle registration on new elements
		 */
		FlowBoardComponent.UI.makeContentInteractive = function ( $container ) {
			if ( !$container.jquery ) {
				$container = $container.$container;
			}

			// Find all load-handlers and trigger them
			$container.find( '.flow-load-interactive' ).add( $container.filter( '.flow-load-interactive' ) ).each( function () {
				var $this = $( this ),
					handlerName = $this.data( 'flow-load-handler' );

				// If this has a special load handler, run it.
				if ( FlowBoardComponent.UI.events.loadHandlers[ handlerName ] ) {
					FlowBoardComponent.UI.events.loadHandlers[ handlerName ].apply( this, [ $this ] );
				}
			} );

			// Find all the forms
			// @todo move this into a flow-load-handler
			$container.find( 'form' ).add( $container.filter( 'form' ) ).each( function () {
				var $this = $( this ),
					initialState = $this.data( 'flow-initial-state' );

				// Trigger for flow-actions-disabler
				$this.find( 'input, textarea' ).trigger( 'keyup' );

				// Find this form's inputs
				$this.find( 'textarea' ).filter( '[data-flow-expandable]').each( function () {
					// @todo Should this be done via TemplateEngine? Maybe don't modify elements within a template?
					var $this = $( this ),
					// If any of these textareas are expandable, compress them at start via pseudo-cloning into inputs.
						attributes = $this.prop( 'attributes' ),
						$input = $( '<input/>' );
					$.each( attributes, function () {
						$input.attr( this.name, this.value );
					} );

					// Store the old textarea as data on the input
					$.data( $input[0], 'flow-compressed', $this[0] );
					// Store the new input as data on the old textarea
					$.data( $this[0], 'flow-expanded', $input[0] );

					// Drop the new input in place
					FlowBoardComponent.UI.Forms.compressTextarea( $this );
				} );

				FlowBoardComponent.UI.Forms.hideForm( $this );
			} );
		};

		/**
		 * Binds event handlers to individual boards
		 * @param {FlowBoardComponent} flowBoard
		 * @todo Move to FlowComponent
		 */
		FlowBoardComponent.UI.bindBoardHandlers = function ( flowBoard ) {
			var $container = flowBoard.$container,
				$board = flowBoard.$board;

			// Load the collapser state from localStorage
			FlowBoardComponent.UI.collapserState( flowBoard );

			// Container handlers
			$container
				.off( '.FlowBoardComponent' )
				.on(
					'click.FlowBoardComponent keypress.FlowBoardComponent',
					'a, input, button, .flow-click-interactive',
					FlowBoardComponent.UI.events.onClickInteractive
				)
				.on( // @todo REMOVE. This is just to stop from following empty links in demo.
					'click.FlowBoardComponent',
					"a[href='#']",
					function () { event.preventDefault(); }
				)
				.on(
					'click.FlowBoardComponent focusin.FlowBoardComponent focusout.FlowBoardComponent',
					'.flow-menu',
					FlowBoardComponent.UI.events.onToggleHoverMenu
				);

			// Board handlers
			$board
				.off( '.FlowBoardComponent' )
				.on(
					'focus.FlowBoardComponent',
					'input.mw-ui-input, textarea',
					FlowBoardComponent.UI.events.onFocusField
				);
		};

		/**
		 * Binds the window and body event handlers for Flow. More efficient than binding for every board.
		 */
		FlowBoardComponent.UI.bindGlobalHandlers = function () {
			if ( _isGlobalBound ) {
				// Don't do these again.
				return;
			}

			_isGlobalBound = true;

			// Handle scroll to update the topic navigation sidebar
			$( window )
				.on(
					'scroll.flow',
					$.throttle( 50, FlowBoardComponent.UI.events.onWindowScroll )
				)
				.trigger( 'scroll.flow' );
		};

		/**
		 * Triggered by window.scroll. It iterates over every FlowBoard's topic navigator list, affixes it to the
		 * viewport, and will apply the necessary adjustments to show the active topic and its read progress.
		 * @todo This should probably be done in TemplateEngine.
		 */
		FlowBoardComponent.UI.adjustTopicNavigationSidebar = function () {
			var $temp        = $( '<div/>', { 'class': 'flow-topic-navigator', 'style': 'display: none;' } ).appendTo( 'body' ),
				unreadColor  = $temp.css( 'background-color' ),
				readColor    = $temp.addClass( 'flow-topic-navigator-read' ).css( 'background-color' ),
				scrollTop    = $( window ).scrollTop(),
				windowHeight = $( window ).height();

			$temp.remove();

			$.each( FlowBoardComponent.prototype.getInstances(), function () {
				var $topicNavigation = this.$topicNavigation,
					$this, $topic, offsetTop, outerHeight, percent;

				// Only proceed with this wacky stuff if the navigation bar is currently in use
				if ( !$topicNavigation.is( ':visible' ) ) {
					return;
				}

				// Affix the sidebar to the top of the window after scrolling past it
				offsetTop = $topicNavigation.data( 'affix-top' );
				if ( offsetTop && scrollTop < offsetTop ) {
					// Scrolled back up, unfix it
					$topicNavigation
						.removeClass( 'flow-topic-navigation-fixed' )
						.removeData( 'affix-top' );
				} else if ( !offsetTop ) {
					// Not affixed yet
					offsetTop = $topicNavigation.offset().top;
					if ( scrollTop > offsetTop ) {
						// Needs to be affixed to window viewport
						$topicNavigation
							.data( 'affix-top', offsetTop )
							.css( { 'right': $( window ).width() - $topicNavigation.offset().left - $topicNavigation.outerWidth() } )
							.addClass( 'flow-topic-navigation-fixed' );
					}
				}

				// Iterate over every topic link
				$topicNavigation.find( '.flow-topic-navigator' ).each( function () {
					$topic = $( this.href.substr(this.href.indexOf('#')) );
					if ( !$topic.length ) {
						return;
					}

					$this = $( this );

					offsetTop = $topic.offset().top;
					outerHeight = $topic.outerHeight();
					percent = ( scrollTop - offsetTop + windowHeight ) / outerHeight * 100;

					if ( percent >= 100 ) {
						// Entire topic is scrolled beyond.
						if ( !$this.hasClass( 'flow-topic-navigator-read' ) ) {
							// Give it the read class
							$this
								.removeClass( 'flow-topic-navigator-active' )
								.addClass( 'flow-topic-navigator-read' )
								.css( { background: '' } );
						}
					} else if ( percent <= 0.1 ) {
						// Topic is not at all in viewport.
						$this
							.removeClass( 'flow-topic-navigator-active' )
							.removeClass( 'flow-topic-navigator-read' )
							.css( { background: '' } );
					} else {
						// Part of topic is in view. Do partial background progress bar.

						if (percent < 0.01) {
							percent = 0;
						}
						$this
							.removeClass( 'flow-topic-navigator-read' )
							.addClass( 'flow-topic-navigator-active' )
							.css( { background: '-moz-linear-gradient(left,  ' + readColor + ' ' + percent + '%, ' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // FF 3.6+
							.css( { background: '-webkit-gradient(linear, left top, right top, color-stop(' + percent + '%,' + readColor + '), color-stop(' + ( percent + 1 ) + '%,' + unreadColor + '))' } ) // Chrome 4+
							.css( { background: '-webkit-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // Chrome 10+
							.css( { background: '-o-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // Opera 11+
							.css( { background: '-ms-linear-gradient(left,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ) // IE10+
							.css( { background: 'linear-gradient(to right,  ' + readColor + ' ' + percent + '%,' + unreadColor + ' ' + ( percent + 1 ) + '%)' } ); // CSS3
					}
				} );
			} );
		};

		/**
		 * Called on window.scroll. Checks to see if a FlowBoard needs to have more content loaded.
		 * @todo Find a better way than directly targetting the button by class
		 */
		FlowBoardComponent.UI.infiniteScrollCheck = function () {
			var windowPosition = $( window ).scrollTop() + $( window ).height();

			$.each( FlowBoardComponent.prototype.getInstances(), function () {
				var flowBoard = this;


			} );
		};

		/**
		 * Sets the Collapser state to newState, and will load this state on next page refresh.
		 * @param {FlowBoardComponent} flowBoard
		 * @param {String} [newState]
		 */
		FlowBoardComponent.UI.collapserState = function ( flowBoard, newState ) {
			if ( !newState ) {
				// Get last
				newState = mw.flow.StorageEngine.localStorage.getItem( 'collapserState' ) || 'full';
			} else {
				// Save
				mw.flow.StorageEngine.localStorage.setItem( 'collapserState', newState );
				flowBoard.$board.find( '.flow-topic-collapsed-invert' ).removeClass( 'flow-topic-collapsed-invert' );

				// Remove individual topic states
				mw.flow.StorageEngine.sessionStorage.removeItem( 'topicCollapserStates' );
			}

			flowBoard.$container
				.removeClass( 'flow-board-collapsed-full flow-board-collapsed-topics flow-board-collapsed-compact' )
				.addClass( 'flow-board-collapsed-' + newState );
		};
	}() );
}( jQuery, mediaWiki ) );
