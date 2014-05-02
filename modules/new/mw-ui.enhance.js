/*!
 * Enhances mediawiki-ui style elements with JavaScript.
 */

( function ( $ ) {
	/*
	* Reduce eye-wandering due to adjacent colorful buttons
	* This will make unhovered and unfocused sibling buttons become faded and blurred
		* Usage: Buttons must be in a form, or in a parent with flow-ui-button-container, or they must be siblings
	*/
	$( document ).ready( function () {
		function onMwUiButtonFocus( event ) {
			var $el, $form, $siblings;

			if ( event.target.className.indexOf( 'flow-ui-button' ) === -1 ) {
				// Not a button event
				return;
			}

			$el = $( event.target );

			if ( event.type !== 'keyup' || $el.is( ':focus' ) ) {
				// Reset style
				$el.removeClass( 'flow-ui-button-althover' );

				$form = $el.closest( 'form, .flow-ui-button-container' );
				if ( $form.length ) {
					// If this button is in a form, apply this to all the form's buttons.
					$siblings = $form.find( '.flow-ui-button' );
				} else {
					// Otherwise, try to find neighboring buttons
					$siblings = $el.siblings( '.flow-ui-button' );
				}

				// Add fade/blur to unfocused sibling buttons
				$siblings.not( $el ).filter( ':not(:focus)' )
					.addClass( 'flow-ui-button-althover' );
			}
		}

		function onMwUiButtonBlur( event ) {
			if ( event.target.className.indexOf( 'flow-ui-button' ) === -1 ) {
				// Not a button event
				return;
			}

			var $el       = $( event.target ),
				$form, $siblings, $focused;

			$form = $el.closest( 'form, .flow-ui-button-container' );
			if ( $form.length ) {
				// If this button is in a form, apply this to all the form's buttons.
				$siblings = $form.find( '.flow-ui-button' );
			} else {
				// Otherwise, try to find neighboring buttons
				$siblings = $el.siblings( '.flow-ui-button' );
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
				$siblings.removeClass( 'flow-ui-button-althover' );
			}
		}

		// Attach the mouseenter and mouseleave handlers on document
		$( document )
			.on( 'mouseenter.mw-ui-enhance', '.flow-ui-button', onMwUiButtonFocus )
			.on( 'mouseleave.mw-ui-enhance', '.flow-ui-button', onMwUiButtonBlur );

		// Attach these independently, because jQuery doesn't support useCapture mode (focus propagation)
		if ( document.attachEvent ) {
			document.attachEvent( 'focusin', onMwUiButtonFocus );
			document.attachEvent( 'focusout', onMwUiButtonBlur );
		} else {
			document.body.addEventListener( 'focus', onMwUiButtonFocus, true );
			document.body.addEventListener( 'blur', onMwUiButtonBlur, true );
		}
	} );


	/*
	 * Disable / enable preview and submit buttons without/with text in field.
	 * Usage: field needs required attribute
	 */
	$( document ).ready( function () {
		$( document ).on( 'keyup.flow-actions-disabler', '.mw-ui-input', function () {
			var $form = $( this ).closest( 'form' ),
				$fields = $form.find( 'input, textarea' ).filter( '[required]' ),
				ready = true;

			$fields.each( function () {
				if ( this.value === '' ) {
					ready = false;
				}
			} );

			// @todo scrap data-role? use submit types? or a single role=action?
			$form.find( '.flow-ui-button' ).filter( '[data-role=action], [data-role=submit]' )
				.attr( 'disabled', !ready );
		} );
	} );


	/*
	 *
	 */
	$( document ).ready( function () {
		var $tooltip = $( '<span class="flow-ui-tooltip flow-ui-tooltip-left"><span class="flow-ui-tooltip-content"></span><span class="flow-ui-tooltip-triangle"></span></span>' ).appendTo( 'body' );

		/**
		 *
		 * @param {Event} event
		 */
		function onMwUiTooltipFocus( event ) {
			var $el = $( this ),
				is_mini = true,
				tooltype = $el.data( 'tooltip-type' ),
				context = $el.data( 'tooltip-context' ),
				contentTarget = $el.data( 'tooltip-content-target' ), // @todo
				elOffset = $el.offset(),
				elWidth = $el.outerWidth(),
				elHeight = $el.outerHeight(),
				windowWidth = $( window ).width(),
				windowHeight = $( window ).width(),
				windowScroll = $( window ).scrollTop(),
				cssPosition = {
					position: 'absolute',
					zIndex: 1000,
					left:   '',
					top:    ''
				},
				target;


			// Determine type of tooltip
			switch ( tooltype ) {
				// @todo
				case 'sticky':
					break;

				// On hover/focus, remove on blur
				default:
					if ( $el[0].title ) {
						$el.data( 'title', $el[0].title );
						$el[0].title = '';
					}
					$tooltip.find( '.flow-ui-tooltip-content' ).text( $el.data( 'title' ) );
					context = context || 'progressive';
					break;
			}

			// Does this tooltip have a stylized context?
			$tooltip.removeClass( 'flow-ui-progressive flow-ui-regressive flow-ui-constructive flow-ui-destructive' );
			if ( context ) {
				$tooltip.addClass( 'flow-ui-' + context );
			}
			// Is this a mini tooltip?
			if ( is_mini ) {
				$tooltip.addClass( 'flow-ui-tooltip-mini' );
			} else {
				$tooltip.removeClass( 'flow-ui-tooltip-mini' );
			}

			// Figure out where to place the tooltip
			$tooltip.removeClass( 'flow-ui-tooltip-down flow-ui-tooltip-up flow-ui-tooltip-left flow-ui-tooltip-right' );
			if ( elOffset.left + elWidth / 2 < windowWidth / 2 ) {
				// Element is on left half of screen
				if ( elOffset.top + elHeight / 2 < windowScroll + windowHeight / 2 ) {
					// Element is on top half of screen
					target = 'up';
				} else {
					// Element is on bottom half of screen
					target = 'down';
				}
			} else {
				// Element is on right half of screen
				if ( elOffset.top + elHeight / 2 < windowScroll + windowHeight / 2 ) {
					// Element is on top half of screen
					target = 'up';
				} else {
					// Element is on bottom half of screen
					target = 'down';
				}
			}

			// Position it
			$tooltip.show();
			switch ( target ) {
				case 'down':
					cssPosition.left = elOffset.left + elWidth / 2 - $tooltip.outerWidth() / 2;
					cssPosition.top = elOffset.top - $tooltip.outerHeight();
					break;
				case 'up':
					cssPosition.left = elOffset.left + elWidth / 2 - $tooltip.outerWidth() / 2;
					cssPosition.top = elOffset.top + elHeight;
					break;
				case 'left':
					break;
				case 'right':
					break;
			}

			$tooltip.addClass( 'flow-ui-tooltip-' + target );
			$tooltip.css( cssPosition );
		}

		/**
		 *
		 * @param {Event} event
		 */
		function onMwUiTooltipBlur( event ) {
			var $el = $( this );
			$tooltip.hide();
			$el[0].title = $el.data( 'title' );
		}

		// Attach the mouseenter and mouseleave handlers on document
		$( document )
			.on( 'mouseenter.mw-ui-enhance focus.mw-ui-enhance click.mw-ui-enhance', '.flow-ui-tooltip-target', onMwUiTooltipFocus )
			.on( 'mouseleave.mw-ui-enhance blur.mw-ui-enhance', '.flow-ui-tooltip-target', onMwUiTooltipBlur );
	} );
}( jQuery ) );
