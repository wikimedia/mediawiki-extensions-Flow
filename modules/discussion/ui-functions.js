( function( $, mw ) {
	$.fn.extend( true, {
		'flow' : function() {
			var args = Array.prototype.slice.call( arguments ),
				funcName = args.shift();

			return this._flow[funcName].apply( this, args );
		},

		'_flow' : {
		'setupEmptyDisabler' : function( fieldSelectors, submitSelector ) {
			var $form = this,
				validateFunction = function( $container ) {
					var isOk = true;

					$.each( fieldSelectors, function() {
						// I have no idea why "toString()" is necessary
						if ( !$container.find( this.toString() ).val() ) {
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

			$.each( fieldSelectors, function() {
				$form.find( this.toString() )
					.not( '.flow-disabler' )
					.keyup( function( e ) {
						var $container = $( e.target ).closest( $form );
						validateFunction( $container );
					} )
					.addClass( 'flow-disabler' );
			} );

			$form.each( function() {
				validateFunction( $( this ) );
			} );

			return $form;
		},

		'setupFormHandler' : function(
			submitSelector,
			submitFunction,
			loadParametersCallback,
			validateCallback,
			promiseCallback
		) {
			var $container = this;

			$container.find( submitSelector )
				.click( function( e ) {
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
						.done( function( output ) {
							$spinner.remove();
							$button.show();
							$form.find( '.flow-cancel-link' )
								.click();

							deferredObject.resolve.apply( $button, arguments );
						} )
						.fail( function() {
							var $errorDiv = $( '<div/>' ).flow( 'showError', arguments ),
								$disclaimer = $form.find( '.flow-disclaimer' );

							$spinner.remove();
							$button.show();

							if ( $disclaimer.length ) {
								$errorDiv.insertBefore( $disclaimer );
							} else {
								$form.append( $errorDiv );
							}

							deferredObject.reject.apply( $button, arguments );
						} );
				} );

			return $container;
		},

		'getTopicWorkflowId' : function() {
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

		'getWorkflowParameters' : function() {
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

		'showError' : function( errorArgs ) {
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
				$.each( errorArgs[1], function( block, blockErrors ) {
					$.each( blockErrors, function( field, errorMsg ) {
						errors.push( errorMsg );
					} );
				} );

				if ( errors.length === 1 ) {
					$errorDiv.html( mw.message( 'flow-error-external', errors.pop() ).text() );
				} else if ( errors.length > 1 ) {
					$.each( errors, function( k, error ) {
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
	} } );
} )( jQuery, mediaWiki );
