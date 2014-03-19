( function ( $, mw ) {
	$.fn.extend( true, {
		/**
		 * @param {string} funcName Name of _flow method
		 * @param {...mixed} [argv] Arguments to respective _flow method
		 * @return {mixed}
		 */
		'flow' : function ( funcName, argv ) {
			var args = Array.prototype.slice.call( arguments );
			args.shift();

			return this._flow[funcName].apply( this, args );
		},

		'_flow' : {
			'registerInitFunction' : function( callback ) {
				$( this ).on( 'flow_init', callback );

				$( '.flow-initialised' ).each( function() {
					callback.apply( this, [ {
						'type' : 'flow_init',
						'target' : this
					} ] );
				} );
			},

			/**
			 * Checks on every keyup if all fieldSelectors are filled out and
			 * (if so) enabled submitSelector.
			 *
			 * @param {array} fieldSelectors Array of jQuery selector strings
			 * @param {string} submitSelector jQuery selector string
			 * @return {jQuery}
			 */
			'setupEmptyDisabler' : function ( fieldSelectors, submitSelector ) {
				var $form = this,
					validateFunction = function ( $container ) {
						var isOk = true;

						$.each( fieldSelectors, function () {
							// I have no idea why "toString()" is necessary
							var $node = $container.find( this.toString() ),
								hasEditor = $node.data( 'flow-editor' );

							// check if any of the selectors has no content
							if (
								!( hasEditor && mw.flow.editor.getRawContent( $node ) ) &&
								!( !hasEditor && $node.val() )
							) {
								isOk = false;
								return false; // break
							}
						} );

						if ( isOk ) {
							$container.find( submitSelector )
								.prop( 'disabled', false );
						} else {
							$container.find( submitSelector )
								.prop( 'disabled', true );
						}
					};

				$.each( fieldSelectors, function () {
					$form.find( this.toString() )
						.not( '.flow-disabler' )
						.keyup( function ( e ) {
							var $container = $( e.target ).closest( $form );
							validateFunction( $container );
						} )
						.addClass( 'flow-disabler' );
				} );

				$form.each( function () {
					validateFunction( $( this ) );
				} );

				return $form;
			},

			/**
			 * @param {string} submitSelector jQuery selector string to capture submit button
			 * @param {function} submitFunction Function to execute when submitting form
			 * @param {function} loadParametersCallback Function to load parameters to be submitted
			 * @param {function} validateCallback Function to validate parameters to be submitted
			 * @param {function} promiseCallback Callback to be executed when form is submitted
			 * @return {jQuery}
			 */
			'setupFormHandler' : function (
				submitSelector,
				submitFunction,
				loadParametersCallback,
				validateCallback,
				promiseCallback
			) {
				var $container = $( this ),
					handler = $( this ).flow(
						'getFormHandler',
						submitFunction,
						loadParametersCallback,
						validateCallback,
						promiseCallback
					);

				$container.find( submitSelector )
					.on( 'click.mw-flow-discussion', function ( e ) {
						e.preventDefault();
						handler.apply( e.target, [e] );
					} );

				return $container;
			},

			'getFormHandler' : function(
				submitFunction,
				loadParametersCallback,
				validateCallback,
				promiseCallback
			) {
				return function() {
					var $button = $( this ),
						$form = $button.closest( 'form' ),
						params = loadParametersCallback.apply( this ),
						$spinner,
						deferredObject = $.Deferred();

					$form.find( '.flow-error' )
						.remove();

					if ( validateCallback && !validateCallback.apply( this, params ) ) {
						$button.prop( 'disabled', true );
						if (window.console) {
							console.log( 'Validate callback failed' );
						}
						return;
					}

					$button.hide();
					$spinner = $.createSpinner().insertAfter( $button );

					if ( promiseCallback ) {
						promiseCallback( deferredObject.promise() );
					}

					submitFunction.apply( this, params )
						.always( function () {
							$spinner.remove();
							$button.show();
						} )
						.done( function ( output ) {
							deferredObject.resolve.apply( $button, arguments );
						} )
						.fail( function () {
							var $errorDiv = $( '<div/>' ).flow( 'showError', arguments );

							if ( $form ) {
								// Remove previous error
								$form.children( ':first' ).filter('.flow-preview-error' ).remove();
								// Append new error
								$form.append( $errorDiv );
							}

							deferredObject.reject.apply( $button, arguments );
						} );
				};
			},

			/**
			 * @return {string}
			 */
			'getTopicWorkflowId' : function () {
				var $element = this,
					$topicContainer = $element.closest( '.flow-topic-container' ),
					$container = $element.closest( '.flow-container' );

				if ( $topicContainer.length ) {
					return $topicContainer.data( 'topic-id' );
				} else if ( $container.length ) {
					return $container.data( 'workflow-id' );
				} else {
					if ( window.console && console.dirxml ) {
						console.dirxml( $element[0] );
					}
					throw new Error( 'Unable to get a workflow ID' );
				}
			},

			/**
			 * @return {object}
			 */
			'getWorkflowParameters' : function () {
				var $container = this.closest( '.flow-container' ),
					workflowStatus = $container.data( 'workflow-existence' );

				if ( workflowStatus === 'new' ) {
					return {
						'page' : $container.data( 'page-title' )
					};
				} else if ( workflowStatus === 'existing' ) {
					return {
						'workflow' : $container.data( 'workflow-id' )
					};
				} else {
					throw new Error( 'Unknown workflow status ' + workflowStatus );
				}
			},

			/**
			 * @param {array} errorArgs
			 * @returns {jQuery}
			 */
			'showError' : function ( errorArgs ) {
				var $errorDiv = this,
					apiExceptionPrefix = 'internal_api_error',
					errors = [],
					$errorList = $( '<ul/>' );

				$errorDiv
					.addClass( 'flow-error errorbox' )
					.hide()
					.slideDown( 'fast', function () {
						$errorDiv.conditionalScrollIntoView();
					} );

				if ( errorArgs[0] === 'http' ) {
					// HTTP error occurred
					$errorDiv.html( mw.message( 'flow-error-http' ).text() );
				} else if ( errorArgs[0].substr( 0, apiExceptionPrefix.length ) === apiExceptionPrefix ) {
					$errorDiv.html( mw.message( 'flow-error-external', errorArgs[1].error.info ).text() );
					console.dir( errorArgs[1] );
				} else if ( errorArgs[0] === 'block-errors' ) {
					$.each( errorArgs[1], function ( block, blockErrors ) {
						$.each( blockErrors, function ( field, error ) {
							errors.push( error.message );
						} );
					} );

					if ( errors.length === 1 ) {
						$errorDiv.html( mw.message( 'flow-error-external', errors.pop() ).text() );
					} else if ( errors.length > 1 ) {
						$.each( errors, function ( k, error ) {
							$( '<li />' ).text( error )
								.appendTo( $errorList );
						} );
						$errorDiv.html(
							mw.message(
								'flow-error-external-multi',
								// .html() returns inner html, so wrap another node around it
								$( '<div>' ).append( $errorList ).html()
							).text()
						);
					} else {
						$errorDiv.html( mw.message( 'flow-error-other' ) );
					}
				} else {
					$errorDiv.html( mw.message( 'flow-error-other' ).text() );
					console.dir( errorArgs );
				}

				return $errorDiv;
			},

			/**
			 * Setup tipsy function
			 * @param {jQuery} $flyout the JQuery object that contains tipsy content
			 * @param {string} action the action listener that triggers the tipsy
			 */
			'setupTipsy': function( $flyout, action ) {
				var $element = this, tipsyCallback = function( e ) {
					e.preventDefault();
					// close other tipsy that may be open
					$( '.flow-tipsy-open' ).each( function() {
						$( this )
							.removeClass( 'flow-tipsy-open' )
							.tipsy( 'hide' );
					} );

					$( this ).addClass( 'flow-tipsy-open' );
					$( this ).tipsy( 'show' );
				};
				$element.tipsy( {
					fade: true,
					gravity: function() {
						return $( this ).data( 'tipsy-gravity') || 'n' ;
					},
					html: true,
					trigger: 'manual',
					title: function() {
						var $clone = $flyout.clone();
						return $( '<div>' ).append( $clone ).html();
					}
				} );
				if ( !action ) {
					action = 'click';
				}
				$element.on( action + '.mw-flow-discussion', tipsyCallback );
			},

			/**
			 * Setup preview function
			 * The key specifies the content identifier in the form and value is output format
			 * @param {object|null} contents
			 * @param string the div css
			 * @todo Revamp this; these handlers should be assigned in base.js
			 */
			'setupPreview': function( contents, css ) {
				var $form = this,
					api = new mw.Api(),
					$previewContainer = $( '<div>', { 'class': 'flow-content-preview' } ),
					$spinner = $();

				// Default output format is parsed, the text identifier is textarea
				if ( !contents ) {
					contents = { 'textarea': 'parsed' };
				}

				// Event handlers
				/**
				 * onclick handler for preview button
				 * @param {Event} event
				 */
				function previewClick( event ) {
					event.preventDefault();

					var $div, identifier;

					// Hide and empty the old preview, if any
					$previewContainer.hide().empty();
					$spinner.remove();

					// Make a spinner while we load this preview
					$spinner = $.createSpinner().insertAfter( this );

					// Iterate over each content type and parse them
					for ( identifier in contents ) {
						$div = $( '<div>', { 'class': 'flow-preview-sub-container' } );
						// Add additional css styling
						if ( css ) {
							$div.addClass( css );
						}
						$previewContainer.append( $div );
						if ( contents[identifier] === 'parsed' ) {
							if ( mw.flow.editor.getFormat() !== 'html' ) {
								// HTML needs to be sent to the back-end for results
								api.post( {
									action: 'flow-parsoid-utils',
									from: mw.flow.editor.getFormat(),
									to: 'html',
									content: $form.find( identifier ).val(),
									title: mw.config.get( 'wgPageName' )
								} )
									// On success
									.done( $.proxy( previewDone, $div ) )
									// On failure
									.fail( $.proxy( previewFail, $div ) );

								continue;
							}
						}

						// If no api request needs to be made, call previewDone instantly
						previewDone.call(
							$div, {
								'flow-parsoid-utils': {
									content: $form.find( identifier ).val()
								}
							}
						);
					}
				}

				/**
				 * Preview XHR success handler. Outputs the HTML content to the appropriate div.
				 * this should be bound to $div
				 * @param {object} data
				 */
				function previewDone( data ) {
					// Load the content
					$( this ).html( data['flow-parsoid-utils'].content );

					// Show the preview container and bring it into view
					$previewContainer.show().conditionalScrollIntoView();

					// Destroy the spinner
					$spinner.remove();
				}

				/**
				 * Preview XHR failure handler. Outputs an error to the form.
				 * this should be bound to $div
				 * @param {string} code
				 * @param {object} data
				 */
				function previewFail( code, data ) {
					// Remove previous error
					$form.children( ':first' ).filter( '.flow-preview-error' ).remove();

					// Create new error and initialize it
					$( '<div>', { 'class': 'flow-preview-error' } ).flow( 'showError', arguments ).prependTo( $form );

					// Destroy the spinner
					$spinner.remove();
				}

				// Now let's do stuff...
				$form
					// Kill old handlers
					.off( 'click.mw-flow-discussion' )
					// Bind the preview button click event onto the form
					.on( 'click.mw-flow-discussion', '.flow-preview-submit', previewClick )
					// And insert the preview container into this form
					.prepend( $previewContainer );

				// Create preview button and pop it in before the cancel button
				$( '<input />', {
					'type': 'submit',
					'value': mw.msg( 'flow-preview' ),
					'class': 'mw-ui-button flow-preview-submit'
				} )
				// add some whitespace to separate from what's next ;)
				.after( ' ' )
				.insertAfter( $form.find( '.flow-cancel-link' ) );

				// allow chaining
				return this;
			},

			/**
			 * Hide the preview
			 */
			'hidePreview': function() {
				this.find( '.flow-content-preview' ).empty().hide();

				// allow chaining
				return this;
			}
		}
	} );
} )( jQuery, mediaWiki );
