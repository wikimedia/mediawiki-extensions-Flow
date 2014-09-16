/*!
 * Enhances mediawiki-ui style elements with JavaScript.
 */

( function ( mw, $ ) {
	/*
	* Reduce eye-wandering due to adjacent colorful buttons
	* This will make unhovered and unfocused sibling buttons become faded and blurred
		* Usage: Buttons must be in a form, or in a parent with mw-ui-button-container, or they must be siblings
	*/
	$( document ).ready( function () {
		function onMwUiButtonFocus( event ) {
			var $el, $form, $siblings;

			if ( event.target.className.indexOf( 'mw-ui-button' ) === -1 ) {
				// Not a button event
				return;
			}

			$el = $( event.target );

			if ( event.type !== 'keyup' || $el.is( ':focus' ) ) {
				// Reset style
				$el.removeClass( 'mw-ui-button-althover' );

				$form = $el.closest( 'form, .mw-ui-button-container' );
				if ( $form.length ) {
					// If this button is in a form, apply this to all the form's buttons.
					$siblings = $form.find( '.mw-ui-button' );
				} else {
					// Otherwise, try to find neighboring buttons
					$siblings = $el.siblings( '.mw-ui-button' );
				}

				// Add fade/blur to unfocused sibling buttons
				$siblings.not( $el ).filter( ':not(:focus)' )
					.addClass( 'mw-ui-button-althover' );
			}
		}

		function onMwUiButtonBlur( event ) {
			if ( event.target.className.indexOf( 'mw-ui-button' ) === -1 ) {
				// Not a button event
				return;
			}

			var $el       = $( event.target ),
				$form, $siblings, $focused;

			$form = $el.closest( 'form, .mw-ui-button-container' );
			if ( $form.length ) {
				// If this button is in a form, apply this to all the form's buttons.
				$siblings = $form.find( '.mw-ui-button' );
			} else {
				// Otherwise, try to find neighboring buttons
				$siblings = $el.siblings( '.mw-ui-button' );
			}

			// Add fade/blur to unfocused sibling buttons
			$focused = $siblings.not( $el ).filter( ':focus' );

			if ( event.type === 'mouseleave' && $el.is( ':focus' ) ) {
				// If this button is still focused, but the mouse left it, keep siblings faded
				return;
			} else if ( $focused.length ) {
				// A sibling has focus; have it trigger the restyling
				$focused.trigger( 'mouseenter.mw-ui-enhance' );
			} else {
				// No other siblings are focused; removing button fading
				$siblings.removeClass( 'mw-ui-button-althover' );
			}
		}

		// Attach the mouseenter and mouseleave handlers on document
		$( document )
			.on( 'mouseenter.mw-ui-enhance', '.mw-ui-button', onMwUiButtonFocus )
			.on( 'mouseleave.mw-ui-enhance', '.mw-ui-button', onMwUiButtonBlur );

		// Attach these independently, because jQuery doesn't support useCapture mode (focus propagation)
		if ( document.attachEvent ) {
			document.attachEvent( 'focusin', onMwUiButtonFocus );
			document.attachEvent( 'focusout', onMwUiButtonBlur );
		} else {
			document.body.addEventListener( 'focus', onMwUiButtonFocus, true );
			document.body.addEventListener( 'blur', onMwUiButtonBlur, true );
		}
	} );

	/**
	 * Disables action and submit buttons when a form has required fields
	 * @param {jQuery} $form jQuery object corresponding to a form element.
	 */
	function enableFormWithRequiredFields( $form ) {
		var
			$fields = $form.find( 'input, textarea' ).filter( '[required]' ),
			ready = true;

		$fields.each( function () {
			if ( this.value === '' ) {
				ready = false;
			}
		} );

		// @todo scrap data-role? use submit types? or a single role=action?
		$form.find( '.mw-ui-button' ).filter( '[data-role=action], [data-role=submit]' )
			.attr( 'disabled', !ready );
	}
	/*
	 * Disable / enable preview and submit buttons without/with text in field.
	 * Usage: field needs required attribute
	 */
	$( document ).ready( function () {
		$( document ).on( 'keyup.flow-actions-disabler', '.mw-ui-input', function () {
			enableFormWithRequiredFields( $( this ).closest( 'form' ) );
		} );
	} );


	/*
	 * mw-ui-tooltip
	 * Renders tooltips on over, and also via mw.tooltip.
	 */
	$( document ).ready( function () {
		var _$tooltip = $(
				'<span class="flow-ui-tooltip flow-ui-tooltip-left">' +
					'<span class="flow-ui-tooltip-content"></span>' +
					'<span class="flow-ui-tooltip-triangle"></span>' +
					'<span class="flow-ui-tooltip-close"></span>' +
				'</span>'
			),
			$activeTooltips = $(),
			_mwUiTooltipExpireTimer;

		/**
		 * Renders a tooltip at target.
		 * Options (either given as param, or fetched from target as data-tooltip-x params):
		 *  tooltipSize=String (small,large,block)
		 *  tooltipContext=String (constructive,destructive,progressive,regressive)
		 *  tooltipPointing=String (up,down,left,right)
		 *  tooltipClosable=Boolean
		 *  tooltipContentCallback=Function
		 *
		 * @param {Element} target
		 * @param {Element|String} [content]
		 * @param {Object} [options]
		 */
		function mwUiTooltipShow( target, content, options ) {
			var $target = $( target ),
				// Find previous tooltip for this el
				$tooltip = $target.data( '$tooltip' ),

				// Get window size and scroll details
				windowWidth = $( window ).width(),
				windowHeight = $( window ).height(),
				scrollX = Math.max( window.pageXOffset, document.documentElement.scrollLeft, document.body.scrollLeft ),
				scrollY = Math.max( window.pageYOffset, document.documentElement.scrollTop, document.body.scrollTop ),

				// Store target and tooltip details
				tooltipWidth, tooltipHeight,
				targetPosition,
				locationOrder, tooltipLocation = {},
				insertFn = 'append',

				// Options, no longer by objet reference
				optionsUnreferenced = {},

				i = 0;

			options = options || {};
			// Do this so that we don't alter the data object by reference
			optionsUnreferenced.tooltipSize = options.tooltipSize || $target.data( 'tooltipSize' );
			optionsUnreferenced.tooltipContext = options.tooltipContext || $target.data( 'tooltipContext' );
			optionsUnreferenced.tooltipPointing = options.tooltipPointing || $target.data( 'tooltipPointing' );
			optionsUnreferenced.tooltipContentCallback = options.tooltipContentCallback || $target.data( 'tooltipContentCallback' );
			// @todo closable
			optionsUnreferenced.tooltipClosable = options.tooltipClosable || $target.data( 'tooltipClosable' );

			// Support passing jQuery as argument
			target = $target[0];

			if ( !content ) {
				if ( optionsUnreferenced.tooltipContentCallback ) {
					// Use content callback to get the content for this element
					content = optionsUnreferenced.tooltipContentCallback( target, optionsUnreferenced );

					if ( !content ) {
						return false;
					}
				} else {
					// Check to see if we're simply using target.title as the content
					if ( !target.title ) {
						return false;
					}

					content = target.title;
					$target.data( 'tooltipTitle', content ); // store title
					target.title = ''; // and hide it so it doesn't appear
					insertFn = 'text';

					if ( !optionsUnreferenced.tooltipSize ) {
						// Default size for title tooltip is small
						optionsUnreferenced.tooltipSize = 'small';
					}
				}
			}

			// No previous tooltip
			if ( !$tooltip ) {
				// See if content itself is a tooltip
				try {
					$tooltip = $( content );
				} catch ( e ) {}
				if ( !$tooltip || !$tooltip.is( '.flow-ui-tooltip' ) && !$tooltip.find( '.flow-ui-tooltip' ).length ) {
					// Content is not and does not contain a tooltip, so instead, put content inside a new tooltip wrapper
					$tooltip = _$tooltip.clone();
				}
			}

			// Try to inherit tooltipContext from the target's classes
			if ( !optionsUnreferenced.tooltipContext ) {
				if ( $target.hasClass( 'mw-ui-progressive' ) ) {
					optionsUnreferenced.tooltipContext = 'progressive';
				} else if ( $target.hasClass( 'mw-ui-constructive' ) ) {
					optionsUnreferenced.tooltipContext = 'constructive';
				} else if ( $target.hasClass( 'mw-ui-destructive' ) ) {
					optionsUnreferenced.tooltipContext = 'destructive';
				}
			}

			$tooltip
				// Add the content to it
				.find( '.flow-ui-tooltip-content' )
					.empty()
					[ insertFn ]( content )
					.end()
				// Move this off-page before rendering it, so that we can calculate its real dimensions
				// @todo use .parent() loop to check for z-index and + that to this if needed
				.css( { position: 'absolute', zIndex: 1000, top: 0, left: '-999em' } )
				// Render
				// @todo inject at #bodyContent to inherit (font-)styling
				.appendTo( 'body' );

			// Tooltip style context
			if ( optionsUnreferenced.tooltipContext ) {
				$tooltip.removeClass( 'mw-ui-progressive mw-ui-constructive mw-ui-destructive' );
				$tooltip.addClass( 'mw-ui-' + optionsUnreferenced.tooltipContext );
			}

			// Tooltip size (small, large)
			if ( optionsUnreferenced.tooltipSize ) {
				$tooltip.removeClass( 'flow-ui-tooltip-sm flow-ui-tooltip-lg' );
				$tooltip.addClass( 'flow-ui-tooltip-' + optionsUnreferenced.tooltipSize );
			}

			// Remove the old pointing direction
			$tooltip.removeClass( 'flow-ui-tooltip-up flow-ui-tooltip-down flow-ui-tooltip-left flow-ui-tooltip-right' );

			// tooltip width and height with the new content
			tooltipWidth = $tooltip.outerWidth( true );
			tooltipHeight = $tooltip.outerHeight( true );

			// target positioning info
			targetPosition = $target.offset();
			targetPosition.width = $target.outerWidth( true );
			targetPosition.height = $target.outerHeight( true );
			targetPosition.leftEnd = targetPosition.left + targetPosition.width;
			targetPosition.topEnd = targetPosition.top + targetPosition.height;
			targetPosition.leftMiddle = targetPosition.left + targetPosition.width / 2;
			targetPosition.topMiddle = targetPosition.top + targetPosition.height / 2;

			// Use the preferred pointing direction first
			switch ( optionsUnreferenced.tooltipPointing ) {
				case 'left': locationOrder = [ 'left', 'right', 'left' ]; break;
				case 'right': locationOrder = [ 'right', 'left', 'right' ]; break;
				case 'down': locationOrder = [ 'down', 'up', 'down' ]; break;
				default: locationOrder = [ 'up', 'down', 'up' ];
			}

			do {
				// Position of the POINTER, not the tooltip itself
				switch ( locationOrder[ i ] ) {
					case 'left':
						tooltipLocation.left = targetPosition.leftEnd;
						tooltipLocation.top = targetPosition.topMiddle - tooltipHeight / 2;
						break;
					case 'right':
						tooltipLocation.left = targetPosition.left - tooltipWidth;
						tooltipLocation.top = targetPosition.topMiddle - tooltipHeight / 2;
						break;
					case 'down':
						tooltipLocation.left = targetPosition.leftMiddle - tooltipWidth / 2;
						tooltipLocation.top = targetPosition.top - tooltipHeight;
						break;
					case 'up':
						tooltipLocation.left = targetPosition.leftMiddle - tooltipWidth / 2;
						tooltipLocation.top = targetPosition.topEnd;
						break;
				}

				// Verify tooltip will be mostly visible in viewport
				if (
					tooltipLocation.left > scrollX - 5 &&
					tooltipLocation.top > scrollY - 5 &&
					tooltipLocation.left + tooltipWidth < windowWidth + scrollX + 5 &&
					tooltipLocation.top + tooltipHeight < windowHeight + scrollY + 5
				) {
					break;
				}
				if ( i + 1 === locationOrder.length ) {
					break;
				}
			} while ( ++i <= locationOrder.length );

			// Add the pointing direction class from the loop
			$tooltip.addClass( 'flow-ui-tooltip-' + locationOrder[ i ] );

			// Apply the new location CSS
			$tooltip.css( tooltipLocation );

			// Store this tooltip onto target
			$target.data( '$tooltip', $tooltip );
			// Store this target onto tooltip
			$tooltip.data( '$target', $target );
			// Add this tooltip to our set of active tooltips
			$activeTooltips = $activeTooltips.add( $tooltip );

			// Start the expiry timer
			_mwUiTooltipExpire();

			return $tooltip;
		}

		/**
		 * Hides the tooltip associated with target instantly.
		 * @param {Element|jQuery} target
		 */
		function mwUiTooltipHide( target ) {
			var $target = $( target ),
				$tooltip = $target.data( '$tooltip' ),
				tooltipTitle = $target.data( 'tooltipTitle' );

			// Remove tooltip from DOM
			if ( $tooltip ) {
				$target.removeData( '$tooltip' );
				$activeTooltips = $activeTooltips.not( $tooltip );
				$tooltip.remove();
			}

			// Restore old title; was used for tooltip
			if ( tooltipTitle ) {
				$target[0].title = tooltipTitle;
				$target.removeData( 'tooltipTitle' );
			}
		}

		/**
		 * Runs on a timer to expire tooltips. This is useful in scenarios where a tooltip's target
		 * node has disappeared (removed from page), and didn't trigger a mouseout event. We detect
		 * the target disappearing, and as such remove the tooltip node.
		 */
		function _mwUiTooltipExpire() {
			clearTimeout( _mwUiTooltipExpireTimer );

			$activeTooltips.each( function () {
				var $this = $( this ),
					$target = $this.data( '$target' );

				// Remove the tooltip if this tooltip has been removed,
				// or if target is not visible (hidden or removed from DOM)
				if ( !this.parentNode || !$target.is( ':visible' ) ) {
					// Remove the tooltip from the DOM
					$this.remove();
					// Unset tooltip from target
					$target.removeData( '$tooltip' );
					// Remove the tooltip from our active tooltips list
					$activeTooltips = $activeTooltips.not( $this );
				}
			} );

			if ( $activeTooltips.length ) {
				// Check again in 500ms if we still have active tooltips
				_mwUiTooltipExpireTimer = setTimeout( _mwUiTooltipExpire, 500 );
			}
		}

		/**
		 * MW UI Tooltip access through JS API.
		 */
		mw.tooltip = {
			show: mwUiTooltipShow,
			hide: mwUiTooltipHide
		};

		/**
		 * Event handler for mouse entering on a .mw-ui-tooltip-target
		 * @param {Event} event
		 */
		function onMwUiTooltipFocus( event ) {
			mw.tooltip.show( this );
		}

		/**
		 * Event handler for mouse leaving a .mw-ui-tooltip-target
		 * @param {Event} event
		 */
		function onMwUiTooltipBlur( event ) {
			mw.tooltip.hide( this );
		}

		// Attach the mouseenter and mouseleave handlers on document
		$( document )
			.on( 'mouseenter.mw-ui-enhance focus.mw-ui-enhance', '.flow-ui-tooltip-target', onMwUiTooltipFocus )
			.on( 'mouseleave.mw-ui-enhance blur.mw-ui-enhance click.mw-ui-enhance', '.flow-ui-tooltip-target', onMwUiTooltipBlur );
	} );


	/*
	 * mw-ui-dialog
	 * Renders tooltips on over, and also via mw.tooltip.
	 */
	$( document ).ready( function () {
		/**
		 * Accepts an element or HTML string as contents. If none given,
		 * modal will start in hidden state.
		 * Settings keys:
		 * - open (same arguments as open method)
		 * - title String
		 * - closable Boolean (defaults to true; if false, close button, ESC, and background clicks do not close it)
		 *
		 * @todo Implement multi-step
		 * @todo Implement data-mwui handlers
		 * @todo Implement closable
		 * @todo Implement OOJS & events
		 *
		 * @example modal = mw.Modal();
		 * @example modal = mw.Modal( { open: 'Contents!!', title: 'Title!!' } );
		 * @example modal = mw.Modal( 'special_modal' );
		 *
		 * @param {String} [name] Name of modal (may be omitted)
		 * @param {Object} [settings]
		 * @return MwUiModal
		 */
		function MwUiModal( name, settings ) {
			// allow calling this method with or without "new" keyword
			if ( this.constructor !== MwUiModal ) {
				return new MwUiModal( name, settings );
			}

			// Defaults and ordering
			if ( !settings && typeof name === 'object' ) {
				settings = name;
				name = null;
			}
			settings = settings || {};

			// Set name
			this.name = name;

			// Set title
			this.setTitle( settings.title );

			// Auto-open
			if ( settings.open ) {
				this.open( settings.open );
			}

			return this;
		}

		/** Stores template
		 * @todo use data-mwui attributes instead of data-flow **/
		MwUiModal.prototype.template = '' +
			'<div class="flow-ui-modal">' +
				'<div class="flow-ui-modal-layout">' +
					'<div class="flow-ui-modal-heading">' +
						'<a href="#" class="mw-ui-anchor mw-ui-quiet mw-ui-destructive flow-ui-modal-heading-prev" data-flow-interactive-handler="modalPrevOrClose"><span class="wikiglyph wikiglyph-x"></span></a>' +
						'<a href="#" class="mw-ui-anchor mw-ui-quiet mw-ui-constructive flow-ui-modal-heading-next" data-flow-interactive-handler="modalNextOrSubmit"><span class="wikiglyph wikiglyph-tick"></span></a>' +
						// title
					'</div>' +

					'<div class="flow-ui-modal-content">' +
						// content
					'</div>' +
				'</div>' +
			'</div>';

		/** Stores modal wrapper selector **/
		MwUiModal.prototype.wrapperSelector = '.flow-ui-modal';
		/** Stores content wrapper selector **/
		MwUiModal.prototype.contentSelector = '.flow-ui-modal-content';
		/** Stores heading wrapper selector, which contains prev/next links **/
		MwUiModal.prototype.headingSelector = '.flow-ui-modal-heading';
		/** Stores prev link selector **/
		MwUiModal.prototype.prevSelector = '.flow-ui-modal-heading-prev';
		/** Stores next link selector **/
		MwUiModal.prototype.nextSelector = '.flow-ui-modal-heading-next';

		// Primary functions

		/**
		 * Closes and destroys the given instance of mw.Modal.
		 *
		 * @return {Boolean} false on failure, true on success
		 */
		MwUiModal.prototype.close = function () {
			// Remove references
			this._contents = this._title = null;

			if ( this.$node ) {
				// Remove whole thing from page
				this.getNode().remove();

				return true;
			}

			return false;
		};

		/**
		 * You can visually render the modal using this method. Opens up by displaying it on the page.
		 *
		 * - Multi-step modals with an Array. You can pass [ Element, Element ] to have two steps.
		 * - Multi-step modals with an Object to have named step keys. Pass this for three steps:
		 *   { steps: [ 'first', 'second', 'foobar' ], first: Element, second: Element, foobar: Element }
		 *
		 * @todo Currently only supports String|jQuery|Element. Implement multi-step modals.
		 *
		 * @param {Array|Object|Element|jQuery|String} [contents]
		 * @return MwUiModal
		 */
		MwUiModal.prototype.open = function ( contents ) {
			var $node = this.getNode(),
				$contentNode = this.getContentNode(),
				$fields;

			// Only update content if it's new
			if ( contents && contents !== this._contents ) {
				this._contents = contents;

				$contentNode
					// Remove children (this way we can unbind events)
					.children()
						.remove()
						.end()
					// Remove any plain text left over
					.empty()
					// Add the new content
					.append( contents );
			}

			// Drop it into the page
			$node.appendTo( 'body' );

			// Show the tick box if we have a type|data-role=submit button available to assign it to; hide it otherwise
			$node.find( this.nextSelector )[
				$contentNode.find( 'a, input, button' ).filter( ':visible' ).filter( '[type=submit], [data-role=submit]' ).length ? 'show' : 'hide'
			]();

			// If something in here did not auto-focus, let's focus something
			$fields = $node.find( 'textarea, input, select' ).filter( ':visible' );
			if ( !$fields.filter( ':focus' ).length ) {
				// Try to focus on an autofocus field
				$fields = $fields.filter( '[autofocus]' );
				if ( $fields.length ) {
					$fields.focus();
				} else {
					// Try to focus on ANY input
					$fields = $fields.end().filter( ':first' );
					if ( $fields.length ) {
						$fields.focus();
					} else {
						// Give focus to the wrapper itself
						$node.focus();
					}
				}
			}

			return this;
		};

		/**
		 * Changes the title of the modal.
		 *
		 * @param {String|null} title
		 * @return MwUiModal
		 */
		MwUiModal.prototype.setTitle = function ( title ) {
			var $heading = this.getNode().find( this.headingSelector ),
				$children;

			title = title || '';

			// Only update title if it's new
			if ( title !== this._title ) {
				this._title = title;

				// Remove any element children temporarily, so we can set the title here
				$children = $heading.children().detach();

				$heading
					// Set the new title
					.text( title )
					// Add the child nodes back
					.prepend( $children );
			}

			// Show the heading if there's a title; hide otherwise
			$heading[ title ? 'show' : 'hide' ]();

			return this;
		};

		/**
		 * @todo Implement data-mwui handlers, currently using data-flow
		 */
		MwUiModal.prototype.setInteractiveHandler = function () {
			return false;
		};

		/**
		 * Returns modal name.
		 */
		MwUiModal.prototype.getName = function () {
			return this.name;
		};

		// Nodes

		/**
		 * Returns the modal's wrapper Element, which contains the header node and content node.
		 * @returns {jQuery}
		 */
		MwUiModal.prototype.getNode = function () {
			var self = this,
				$node = this.$node;

			// Create our template instance
			if ( !$node ) {
				$node = this.$node = $( this.template );

				// Store a self-reference
				$node.data( 'MwUiModal', this );

				// Bind close handlers
				$node.on( 'click', function ( event ) {
					// If we are clicking on the modal itself, it's the outside area, so close it;
					// make sure we aren't clicking INSIDE the modal content!
					if ( this === $node[ 0 ] && event.target === $node[ 0 ] ) {
						self.close();
					}
				} );
			}

			return $node;
		};

		/**
		 * Returns the wrapping Element on which you can bind bubbling events for your content.
		 * @returns {jQuery}
		 */
		MwUiModal.prototype.getContentNode = function () {
			return this.getNode().find( this.contentSelector );
		};

		// Step creation

		/**
		 * Adds one or more steps, using the same arguments as modal.open.
		 * May overwrite steps if any exist with the same key in Object mode.
		 *
		 * @todo Implement multi-step.
		 *
		 * @param {Array|Object|Element|jQuery|String} contents
		 * @return MwUiModal
		 */
		MwUiModal.prototype.addSteps = function ( contents ) {
			return false;
		};

		/**
		 * Changes a given step. If String to does not exist in the list of steps, throws an exception;
		 * int to always succeeds. If the given step is the currently-active one, rerenders the modal contents.
		 * Theoretically, you could use setStep to keep changing step 1 to create a pseudo-multi-step modal.
		 *
		 * @todo Implement multi-step.
		 *
		 * @param {int|String} to
		 * @param {Element|jQuery|String} contents
		 * @return MwUiModal
		 */
		MwUiModal.prototype.setStep = function ( to, contents ) {
			return false;
		};

		/**
		 * Returns an Object with steps, and their contents.
		 *
		 * @todo Implement multi-step.
		 *
		 * @return Object
		 */
		MwUiModal.prototype.getSteps = function ( to, contents ) {
			return {};
		};

		// Step interaction

		/**
		 * For a multi-step modal, goes to the previous step, otherwise, closes the modal.
		 *
		 * @return {MwUiModal|Boolean} false if none, MwUiModal on prev, true on close
		 */
		MwUiModal.prototype.prevOrClose = function () {
			if ( this.prev() === false ) {
				return this.close();
			}
		};

		/**
		 * For a multi-step modal, goes to the next step (if any), otherwise, submits the form.
		 *
		 * @return {MwUiModal|Boolean} false if no next step and no button to click, MwUiModal on success
		 */
		MwUiModal.prototype.nextOrSubmit = function () {
			var $button;

			if ( this.next() === false && this.$node ) {
				// Find an anchor or button with role=primary
				$button = this.$node.find( this.contentSelector ).find( 'a, input, button' ).filter( ':visible' ).filter( '[type=submit], [data-role=submit]' );

				if ( !$button.length ) {
					return false;
				}

				$button.trigger( 'click' );
			}
		};

		/**
		 * For a multi-step modal, goes to the previous step, if any are left.
		 *
		 * @todo Implement multi-step.
		 *
		 * @return {MwUiModal|Boolean} false if invalid step, MwUiModal on success
		 */
		MwUiModal.prototype.prev = function () {
			return false;
		};

		/**
		 * For a multi-step modal, goes to the next step, if any are left.
		 *
		 * @todo Implement multi-step.
		 *
		 * @return {MwUiModal|Boolean} false if invalid step, MwUiModal on success
		 */
		MwUiModal.prototype.next = function () {
			return false;
		};

		/**
		 * For a multi-step modal, goes to a specific step by number or name.
		 *
		 * @todo Implement multi-step.
		 *
		 * @param {int|String} to
		 * @return {MwUiModal|Boolean} false if invalid step, MwUiModal on success
		 */
		MwUiModal.prototype.go = function ( to ) {
			return false;
		};

		/**
		 * MW UI Modal access through JS API.
		 * @example mw.Modal( "<p>lorem</p>" );
		 */
		mw.Modal = MwUiModal;

		/**
		 * Returns an instance of mw.Modal if one is currently being displayed on the page.
		 * If node is given, tries to find which modal (if any) that node is within.
		 * Returns false if none found.
		 *
		 * @param {Element|jQuery} [node]
		 * @return {Boolean|MwUiModal}
		 */
		mw.Modal.getModal = function ( node ) {
			if ( node ) {
				// Node was given; try to find a parent modal
				return $( node ).closest( MwUiModal.prototype.wrapperSelector ).data( 'MwUiModal') || false;
			}

			// No node given; return the last-opened modal on the page
			return $( 'body' ).children( MwUiModal.prototype.wrapperSelector ).filter( ':last' ).data( 'MwUiModal' ) || false;
		};

		// Transforms: automatically map these functions to call their mw.Modal methods globally, on any active instance
		$.each( [ 'close', 'getName', 'prev', 'next', 'prevOrClose', 'nextOrSubmit', 'go' ], function ( i, fn ) {
			mw.Modal[ fn ] = function () {
				var args = Array.prototype.splice.call( arguments, 0, arguments.length - 1 ),
					node = arguments[ arguments.length - 1 ],
					modal;

				// Find the node, if any was given
				if ( !node || ( typeof node.is === 'function' && !node.is( '*' ) ) || node.nodeType !== 1 ) {
					// The last argument to this function was not a node, assume none was intended to be given
					node = null;
					args = arguments;
				}

				// Try to find that modal
				modal = mw.Modal.getModal( node );

				// Call the intended function locally
				if ( modal ) {
					modal[ fn ].apply( modal, args );
				}
			};
		} );
	} );


	/**
	 * Ask a user to confirm navigating away from a page when they have entered unsubmitted changes to a form.
	 */
	var _oldOnBeforeUnload = window.onbeforeunload;
	window.onbeforeunload = function () {
		var uncommitted;

		$( 'input, textarea' ).filter( '.mw-ui-input:visible' ).each( function () {
			if ( $.trim( this.value ) && this.value !== this.defaultValue ) {
				uncommitted = true;
				return false;
			}
		} );

		// Ask the user if they want to navigate away
		if ( uncommitted ) {
			return mw.msg( 'mw-ui-unsubmitted-confirm' );
		}

		// Run the old on beforeunload fn if it exists
		if ( _oldOnBeforeUnload ) {
			return _oldOnBeforeUnload();
		}
	};
}( mw, jQuery ) );
