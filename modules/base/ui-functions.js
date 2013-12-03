( function ( $, mw ) {
	$.fn.extend( true, {
		/**
		 * @param {string} funcName Name of _flow method
		 * @param {mixed} arguments Arguments to respective _flow method
		 * @return {mixed}
		 */
		'flow' : function ( funcName /* [, argument[, argument[, ...]]] */) {
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
			 * @param  {function} loadFunction        Function to call once the form is loaded.
			 * @return {Promise}                      A promise that will be resolved or rejected
			 * when the form submission has returned.
			 */
			'setupEditForm' : function( type, initialContent, submitFunction, loadFunction ) {
				var $contentContainer = $(this);
				var deferredObject = $.Deferred();
				if ( $contentContainer.siblings( '.flow-edit-'+type+'-form' ).length ) {
					// This is a bit yucky, but should behave correctly in most cases
					return deferredObject.promise();
				}

				var $postForm = $('<form/>')
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
								$( '<a/>' )
									.text( mw.msg( 'flow-cancel' ) )
									.addClass( 'flow-cancel-link' )
									.addClass( 'mw-ui-button' )
									.addClass( 'mw-ui-text' )
									.attr( 'href', '#' )
									.click( function ( e ) {
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

				if ( typeof initialContent != 'object' ) {
					initialContent = {
						'content' : initialContent,
						'format' : 'wikitext'
					};
				}

				/*
				 * Setting focus inside an event that grants focus (like
				 * clicking the edit icon), is tricky. This is a workaround.
				 */
				setTimeout( function( $postForm, initialContent, loadFunction ) {
					var $textarea = $postForm.find( 'textarea' );
					mw.flow.editor.load( $textarea, initialContent.content, initialContent.format )
						.done( loadFunction );

					// scroll to the form
					$postForm.scrollIntoView( {
						complete: function () {
							mw.flow.editor.focus( $textarea );
							mw.flow.editor.moveCursorToEnd( $textarea );
						}
					} );
				}.bind( this, $postForm, initialContent, loadFunction ), 0 );

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
					.addClass( 'flow-cancel-link mw-ui-button mw-ui-text' )
					.text( mw.msg( 'flow-cancel' ) )
					.click( function ( e ) {
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

				if ( typeof initialContent != 'object' ) {
					initialContent = {
						'content' : initialContent,
						'format' : 'wikitext'
					};
				}

				/*
				 * Setting focus inside an event that grants focus (like
				 * clicking the reply button), is tricky. This is a workaround.
				 */
				setTimeout( function( $formContainer, initialContent, loadFunction ) {
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
					$formContainer.scrollIntoView( {
						complete: function () {
							mw.flow.editor.focus( $textarea );
							mw.flow.editor.moveCursorToEnd( $textarea );
						}
					} );
				}.bind( this, $formContainer, initialContent, loadFunction ), 0 );

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
				var $container = $(this);
				var handler = $( this ).flow(
					'getFormHandler',
					submitFunction,
					loadParametersCallback,
					validateCallback,
					promiseCallback
				);

				$container.find( submitSelector )
					.click( function ( e ) {
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
								$form.append( $errorDiv );
							}

							// Add error div to arguments to that it can be removed
							// in submitFunction, if the error is recoverable.
							// Can't just do arguments.push(), hence the workaround.
							[].push.call( arguments, $errorDiv );

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
					console.dirxml( $element[0] );
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
					.slideDown( 'fast' );

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
			 * @param {JQuery} the JQuery object that contains tipsy content
			 * @param {string} the action listener that triggers the tipsy
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
				$element.on( action, tipsyCallback );
			},

			/**
			 * Setup preview function
			 * @param {Object Literal} The key specifies the content identifier in the form
			 * and value is output format
			 */
			'setupPreview': function( contents ) {
				var $form = this, api = new mw.Api(),
					$previewContainer = $( '<div>' ).addClass( 'flow-content-preview' );

				// Default output format is parsed
				if ( !contents ) {
					contents = { 'textarea': 'parsed' };
				}

				$form.prepend( $previewContainer );

				// Set up click handler for showing preview
				$( '<input />' )
					.attr( 'type', 'submit' )
					.val( mw.msg( 'flow-preview' ) )
					.addClass( 'mw-ui-button flow-preview-submit' )
					.click( function ( e ) {
						e.preventDefault();
						$previewContainer.empty();
						var success = function ( data ) {
							$div.html( data['flow-parsoid-utils'].content );
						}, failure = function ( code, data ) {
							$( '<div>' ).flow( 'showError', arguments ).insertBefore( $form );
						};
						for ( var identifier in contents ) {
							var $div = $( '<div>' ).addClass( 'flow-preview-sub-container' );
							$previewContainer.append( $div );
							if ( contents[identifier] === 'parsed' ) {
								if ( mw.flow.editor.getFormat() !== 'html' ) {
									api.post( {
										action: 'flow-parsoid-utils',
										from: mw.flow.editor.getFormat(),
										to: 'html',
										content: $form.find( identifier ).val(),
										title: mw.config.get( 'wgPageName' )
									} )
									.done( success )
									.fail( failure );
								} else {
									$div.text( $form.find( identifier ).val() );
								}
							} else {
								$div.text( $form.find( identifier ).val() );
							}
						}
						$previewContainer.show();
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
