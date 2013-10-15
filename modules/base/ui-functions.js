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
								.removeAttr( 'disabled' );
						} else {
							$container.find( submitSelector )
								.attr( 'disabled', 'disabled' );
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
			 * @return {Promise}                      A promise that will be resolved or rejected
			 * when the form submission has returned.
			 */
			'setupEditForm' : function( type, initialContent, submitFunction ) {
					var deferredObject = $.Deferred();
					var $contentContainer = $(this);
					var $postForm = $('<form/>')
						.addClass( 'flow-edit-'+type+'-form' )
						.hide()
						.insertAfter( $contentContainer );

					$postForm
						.append(
							$( '<textarea />' )
								.addClass( 'flow-edit-'+type+'-content' )
						)
						.append(
							$( '<div/>' )
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

					if ( typeof initialContent != 'object' ) {
						initialContent = {
							'content' : initialContent,
							'format' : 'wikitext'
						};
					}

					mw.flow.editor.load( $postForm.find( 'textarea' ), initialContent.content, initialContent.format );

					$contentContainer.hide();

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

				$container.find( submitSelector )
					.click( function ( e ) {
						e.preventDefault();

						var $button = $( this ),
							$form = $button.closest( 'form' ),
							params = loadParametersCallback.apply( this ),
							$spinner = $( '<div/>' ),
							deferredObject = $.Deferred();

						$form.find( '.flow-error' )
							.remove();

						if ( validateCallback && !validateCallback.apply( this, params ) ) {
							$button.attr( 'disabled', 'disabled' );
							console.log( 'Validate callback failed' );
							return;
						}

						$button.hide();
						$spinner
							.addClass( 'flow-loading-indicator' )
							.insertAfter( $button );

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

								$form.append( $errorDiv );

								deferredObject.reject.apply( $button, arguments );
							} );
					} );

				return $container;
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
					.addClass( 'flow-error' )
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
						$.each( blockErrors, function ( field, errorMsg ) {
							errors.push( errorMsg );
						} );
					} );

					if ( errors.length === 1 ) {
						$errorDiv.html( mw.message( 'flow-error-external', errors.pop() ).text() );
					} else if ( errors.length > 1 ) {
						$.each( errors, function ( k, error ) {
							$( '<li />' ).text( error )
								.appendTo( $errorList );
						} );

						$errorDiv.html( mw.message( 'flow-error-external-multi', $errorList.html() ).text() );
					} else {
						$errorDiv.html( mw.message( 'flow-error-other' ) );
					}
				} else {
					$errorDiv.html( mw.message( 'flow-error-other' ).text() );
					console.dir( errorArgs );
				}

				return $errorDiv;
			}
		}
	} );
} )( jQuery, mediaWiki );
