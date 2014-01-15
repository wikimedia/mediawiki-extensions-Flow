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
			 * Sets up an edit form.
			 * @param  {string} type                  The type of edit form (single word)
			 * @param  {string|object} initialContent The content to pre-fill.
			 * An object, with the keys 'content' and 'format'. Or a plain string of wikitext.
			 * @param  {function} submitFunction      Function to call in order to submit the form.
			 * One parameter, the content.
			 * @param {function} loadFunction         Function to call once the form is loaded.
			 * @return {Promise}                      A promise that will be resolved or rejected
			 * when the form submission has returned.
			 */
			'setupEditForm' : function( type, initialContent, submitFunction, loadFunction ) {
				var $contentContainer = $( this ),
					deferredObject = $.Deferred(),
					$postForm;
				if ( $contentContainer.siblings( '.flow-edit-'+type+'-form' ).length ) {
					// This is a bit yucky, but should behave correctly in most cases
					return deferredObject.promise();
				}

				$postForm = $('<form/>')
					.addClass( 'flow-edit-'+type+'-form' )
					.hide()
					.insertAfter( $contentContainer );

				$postForm
					.append(
						$( '<textarea />' )
							.addClass( 'mw-ui-input' )
							.addClass( 'flow-edit-'+type+'-content' )
					)
					.append(
						$( '<div/>' )
							.addClass( 'flow-'+type+'-form-controls' )
							.addClass( 'flow-edit-'+type+'-controls' )
							.append(
								$( '<div>' ).addClass( 'flow-terms-of-use plainlinks' )
								.html( mw.message( 'flow-terms-of-use-edit' ).parse() )
							)
							.append(
								$( '<a/>' )
									.text( mw.msg( 'flow-cancel' ) )
									.addClass( 'flow-cancel-link' )
									.addClass( 'mw-ui-button' )
									.addClass( 'mw-ui-quiet' )
									.attr( 'href', '#' )
									.on( 'click.mw-flow-discussion', function ( e ) {
										e.preventDefault();
										$postForm.slideUp( 'fast',
											function () {
												$contentContainer.show();
												$postForm.remove();
											}
										);
										$postForm.flow( 'hidePreview' );
									} )
							)
							.append( ' ' )
							.append(
								$( '<input />' )
									.attr( 'type', 'submit' )
									.addClass( 'mw-ui-button' )
									.addClass( 'mw-ui-constructive' )
									.addClass( 'flow-edit-'+type+'-submit' )
									.val( mw.msg( 'flow-edit-'+type+'-submit' ) )
							)
							.append(
								$( '<div>' ).addClass( 'clear' )
							)
					)
					.insertAfter( $contentContainer );

				$postForm.flow( 'setupPreview' );

				$contentContainer.hide();
				$contentContainer.siblings( '.flow-datestamp' ).hide();

				$postForm.flow( 'setupFormHandler',
					'.flow-edit-'+type+'-submit',
					submitFunction,
					function () {
						var content = mw.flow.editor.getContent( $postForm.find( '.flow-edit-'+type+'-content' ) );
						return [ content ];
					},
					function ( content ) {
						return content;
					},
					function ( promise ) {
						promise
							.done( function () {
								deferredObject.resolve.apply( $contentContainer, arguments );
							} )
							.fail( function() {
								deferredObject.reject.apply( $contentContainer, arguments );
							} );
					}
				);

				$postForm.flow( 'setupEmptyDisabler',
					[
						'.flow-edit-'+type+'-content'
					],
					'.flow-edit-'+type+'-submit'
				);

				$contentContainer.hide();
				$postForm.show();

				if ( typeof initialContent !== 'object' ) {
					initialContent = {
						'content' : initialContent,
						'format' : 'wikitext'
					};
				}

				/*
				 * Setting focus inside an event that grants focus (like
				 * clicking the edit icon), is tricky. This is a workaround.
				 */
				setTimeout( $.proxy( function( $postForm, initialContent, loadFunction ) {
					var $textarea = $postForm.find( 'textarea' );
					mw.flow.editor.load( $textarea, initialContent.content, initialContent.format )
						.done( loadFunction );

					// scroll to the form
					$postForm.conditionalScrollIntoView().queue( function () {
						mw.flow.editor.focus( $textarea );
						mw.flow.editor.moveCursorToEnd( $textarea );
					} );
				}, this, $postForm, initialContent, loadFunction ), 0 );

				return deferredObject.promise();
			},

			/**
			 * Sets up a reply form.
			 *
			 * Unlike edit forms, reply forms already exit in HTML, we just have
			 * to "activate" some JS magic.
			 *
			 * @param  {string} type                  The type of edit form (single word)
			 * @param  {string|object} initialContent The content to pre-fill.
			 * An object, with the keys 'content' and 'format'. Or a plain string of wikitext.
			 * @param  {function} submitFunction      Function to call in order to submit the form.
			 * One parameter, the content.
			 * @param {function} loadFunction         Function to call once the form is loaded.
			 * @return {Promise}                      A promise that will be resolved or rejected
			 * when the form submission has returned.
			 */
			'loadReplyForm' : function( type, initialContent, submitFunction, loadFunction ) {
				var $formContainer = $( this ),
					$form = $formContainer.find( 'form' ),
					$textarea = $formContainer.find( 'textarea' ),
					deferredObject = $.Deferred();

				if ( $formContainer.hasClass( 'flow-form-active' ) ) {
					// This is a bit yucky, but should behave correctly in most cases
					return deferredObject.promise();
				}

				// add class to identify this form as being active
				$formContainer.addClass( 'flow-form-active' );

				// hide topic reply form
				$formContainer.closest( '.flow-topic-container' ).find( '.flow-topic-reply-container' ).hide();

				// show this form
				$formContainer.show();

				// add cancel link
				$( '<a />' )
					.attr( 'href', '#' )
					.addClass( 'flow-cancel-link mw-ui-button mw-ui-quiet' )
					.text( mw.msg( 'flow-cancel' ) )
					.on( 'click.mw-flow-discussion', function ( e ) {
						e.preventDefault();

						mw.flow.editor.destroy( $textarea );
						$form.flow( 'hidePreview' );

						// when closed, display topic reply form again
						$formContainer
							.closest( '.flow-topic-container' )
							.find( '.flow-topic-reply-container' )
							.show();

						/*
						 * Because we're not entirely killing the forms, we have
						 * to clean up after cancelling a form (e.g. reply-forms
						 * may be re-used across posts - when max threading
						 * depth has been reached)
						 *
						 * After submitting the reply, kill the events that were
						 * bound to the submit button via setupEmptyDisabler and
						 * setupFormHandler.
						 */
						$formContainer.find( '.flow-'+type+'-reply-submit' ).off();
						$formContainer.removeClass( 'flow-form-active' );
						$( this ).remove();
						$formContainer.find( '.flow-content-preview, .flow-preview-submit' ).remove();
					} )
					.after( ' ' )
					.prependTo( $formContainer.find( '.flow-post-form-controls') );

				// setup preview
				$form.flow( 'setupPreview' );

				// setup submission callback
				$formContainer.flow( 'setupFormHandler',
					'.flow-'+type+'-reply-submit',
					submitFunction,
					function () {
						var content = mw.flow.editor.getContent( $formContainer.find( '.flow-'+type+'-reply-content' ) );
						return [ content ];
					},
					function ( content ) {
						return content;
					},
					function ( promise ) {
						promise
							.done( function () {
								deferredObject.resolve.apply( $formContainer, arguments );
							} )
							.fail( function() {
								deferredObject.reject.apply( $formContainer, arguments );
							} );
					}
				);

				// setup disabler (disables submit button until content is entered)
				$formContainer.flow( 'setupEmptyDisabler',
					['.flow-'+type+'-reply-content'],
					'.flow-'+type+'-reply-submit'
				);

				if ( typeof initialContent !== 'object' ) {
					initialContent = {
						'content' : initialContent,
						'format' : 'wikitext'
					};
				}

				/*
				 * Setting focus inside an event that grants focus (like
				 * clicking the reply button), is tricky. This is a workaround.
				 */
				setTimeout( $.proxy( function( $formContainer, initialContent, loadFunction ) {
					var $textarea = $formContainer.find( 'textarea' );

					$textarea
						.removeClass( 'flow-reply-box-closed' )
						// Override textarea height; doesn't need to be too large initially,
						// it'll auto-expand
						.attr( 'rows', '6' )
						// Textarea will auto-expand + textarea padding will cause the
						// resize grabber to be positioned badly (in FF) so get rid of it
						.css( 'resize', 'none' )
						.focus();

					mw.flow.editor.load( $textarea, initialContent.content, initialContent.format )
						.done( loadFunction );

					// scroll to the form
					$formContainer.conditionalScrollIntoView().queue( function () {
						mw.flow.editor.focus( $textarea );
						mw.flow.editor.moveCursorToEnd( $textarea );
					} );
				}, this, $formContainer, initialContent, loadFunction ), 0 );

				return deferredObject.promise();
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
				var $container = $(this ),
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
						console.log( 'Validate callback failed' );
						return;
					}

					$button.hide();
					$spinner = $.createSpinner().insertAfter( $button );

					if ( promiseCallback ) {
						promiseCallback( deferredObject.promise() );
					}

					submitFunction.apply( this, params )
						.done( function ( output ) {
							$spinner.remove();
							$button.show();
							$form.find( '.flow-cancel-link' )
								.click();

							deferredObject.resolve.apply( $button, arguments );
						} )
						.fail( function () {
							var $errorDiv = $( '<div/>' ).flow( 'showError', arguments );

							$spinner.remove();
							$button.show();

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
			 * @param {object} contents
			 * @todo Revamp this; these handlers should be assigned in base.js
			 */
			'setupPreview': function( contents ) {
				var $form = this,
					api = new mw.Api(),
					$previewContainer = $( '<div>', { 'class': 'flow-content-preview' } ),
					$spinner = $();

				// Default output format is parsed
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

					var waitToShow = false,
						$div, identifier;

					// Hide and empty the old preview, if any
					$previewContainer.hide().empty();
					$spinner.remove();

					// Make a spinner while we load this preview
					$spinner = $.createSpinner().insertAfter( this );

					// Iterate over each content type and parse them
					for ( identifier in contents ) {
						$div = $( '<div>', { 'class': 'flow-preview-sub-container' } );
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
									.done( previewDone.bind( $div ) )
									// On failure
									.fail( previewFail.bind( $div ) );

								// Don't show the preview until this request finishes
								waitToShow = true;
							} else {
								$div.text( $form.find( identifier ).val() );
							}
						} else {
							$div.text( $form.find( identifier ).val() );
						}
					}

					// If no XHR to be done, just show the field now.
					if ( !waitToShow ) {
						// Show the preview container and bring it into view
						$previewContainer.show().conditionalScrollIntoView();
						// Destroy the spinner
						$spinner.remove();
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
				} ).insertAfter( $form.find( '.flow-cancel-link' ) );
			},

			/**
			 * Hide the preview
			 */
			'hidePreview': function() {
				this.find( '.flow-content-preview' ).empty().hide();
			}
		}
	} );
} )( jQuery, mediaWiki );
