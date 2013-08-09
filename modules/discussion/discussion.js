( function($, mw) {
mw.flow.discussion = {
	'setupEmptyDisabler' : function( formSelector, fieldSelectors, submitSelector ) {
		var validateFunction = function( $container ) {
			var isOk = true;

			$.each( fieldSelectors, function() {
				// I have no idea why "toString()" is necessary
				if ( ! $container.find( this.toString() ).val() ) {
					isOk = false;
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
				$(this.toString())
					.not('.flow-disabler')
					.keyup( function(e) {
						$container = $(e.target).closest( formSelector );
						validateFunction( $container );
					} )
					.addClass('flow-disabler');
			} );

		$( formSelector ).each( function() {
			validateFunction( $(this) );
		} );
	},

	'getWorkflowParameters' : function( $container ) {
		var workflowStatus = $container.data( 'workflow-existence' );

		if ( workflowStatus == 'new' ) {
			return {
				'page' : $container.data( 'page-title' )
			};
		} else if ( workflowStatus == 'existing' ) {
			return {
				'workflow' : $container.data( 'workflow-id' )
			};
		} else {
			throw new Error( "Unknown workflow status " + workflowStatus );
		}
	},

	'setupFormHandler' : function(
		$container,
		submitSelector,
		submitFunction,
		loadParametersCallback,
		validateCallback,
		promiseCallback
	) {
		$container.find( submitSelector )
			.click( function(e) {
				e.preventDefault();
				var $button = $( this );
				var $form = $button.closest( 'form' );

				$form.find( '.flow-error' )
					.remove();

				var params = loadParametersCallback.apply( this );

				if ( validateCallback && ! validateCallback.apply( this, params ) ) {
					$button.attr( 'disabled', 'disabled' );
					console.log( "Validate callback failed" );
					return;
				}

				$button.hide();
				var $spinner = $( '<div/>' )
					.addClass( 'flow-loading-indicator' )
					.insertAfter( $button );
				var deferredObject = $.Deferred();

				if ( promiseCallback ) {
					promiseCallback( deferredObject.promise() );
				}

				submitFunction.apply( this, params )
					.done( function(output) {
						$spinner.remove();
						$button.show();
						$form.find( '.flow-cancel-link' )
							.click();
						
						deferredObject.resolve.apply( $button, arguments );
					} )
					.fail( function( ) {
						$spinner.remove();
						$button.show();
						var $errorDiv = $( '<div/>' )
							.addClass( 'flow-error' )
							.hide()
							.slideDown( 'fast' );

						var $disclaimer = $form.find( '.flow-disclaimer' );
						if ( $disclaimer.length ) {
							$errorDiv.insertBefore( $disclaimer );
						} else {
							$form.append( $errorDiv );
						}

						mw.flow.discussion.handleError( $errorDiv, arguments );

						deferredObject.reject.apply( $button, arguments );
					} );
			} );
	},

	'setupRegion' : function( $container ) {
		// Set up menus
		$container.find( '.flow-post-actions, .flow-topic-actions' )
			.click( function(e) {
				$(this).children( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
					.show();
			} );

		// Set up reply form
		$container.find( '.flow-reply-form textarea' )
			.addClass( 'flow-reply-box-closed' )
			.click( function(e) {
				$(this).removeClass( 'flow-reply-box-closed' );
				$(this).closest( 'form' )
					.children( '.flow-post-form-extras' )
					.show();
			} );

		$container.find( '.flow-post-form-extras' )
			.hide();

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-destructive' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function(e) {
				e.preventDefault();
				$(this).closest( '.flow-post-form-extras' )
					.slideUp( 'fast', function() {
						$(this).closest( '.flow-reply-form' )
							.find( 'textarea' )
								.addClass( 'flow-reply-box-closed' )
								.val('')
								.end()
							.find( '.flow-error' )
								.remove();
					});
			} )
			.insertBefore( $container.find('.flow-reply-form input[type=submit]') );

		mw.flow.discussion.setupEmptyDisabler(
			'form.flow-reply-form',
			['.flow-reply-content'],
			'.flow-reply-submit'
		);

		// Set up new topic form
		$container.find( '.flow-newtopic-step2' ).hide();
		$container.find( '.flow-newtopic-title' )
			.addClass( 'flow-newtopic-start' )
			.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
			.click( function(e) {
				if ( ! $(this).hasClass('flow-newtopic-start' ) ) {
					return;
				}
				$( '.flow-newtopic-step2' )
					.show();
				$(this)
					.removeClass( 'flow-newtopic-start' )
					.attr( 'placeholder', mw.msg( 'flow-newtopic-title-placeholder' ) );
				$( '.flow-newtopic-submit' )
					.attr( 'disabled', 'disabled' );
			} );

		mw.flow.discussion.setupEmptyDisabler(
			'form.flow-newtopic-form',
			[
				'.flow-newtopic-title',
				'.flow-newtopic-content'
			],
			'.flow-newtopic-submit'
		);

		$( '<a />' )
			.attr( 'href', '#' )
			.addClass( 'flow-cancel-link' )
			.addClass( 'mw-ui-destructive' )
			.text( mw.msg( 'flow-cancel' ) )
			.click( function(e) {
				e.preventDefault();
				var $form = $(this).closest( 'form.flow-newtopic-form' );
				$( '.flow-newtopic-step2' )
					.slideUp( 'fast', function() {
						$form.find( '.flow-newtopic-title' )
							.val( '' )
							.attr( 'placeholder', mw.msg( 'flow-newtopic-start-placeholder' ) )
							.addClass( 'flow-newtopic-start' );
						$form.find( '.flow-newtopic-content' )
							.val( '' );
						$form.find( '.flow-error' )
							.remove();
					} );
			} )
			.insertBefore( $container.find('.flow-newtopic-form input[type=submit]') );

		// Overload 'new topic' handler.
		mw.flow.discussion.setupFormHandler(
			$container,
			'.flow-newtopic-submit',
			mw.flow.api.newTopic,
			function() {
				$form = $(this).closest( '.flow-newtopic-form' );

				var workflowParam = mw.flow.discussion.getWorkflowParameters( $container );
				var title = $form.find( '.flow-newtopic-title' ).val();
				var content = $form.find( '.flow-newtopic-content' ).val();

				return [ workflowParam, title, content ];
			},
			function( workflowParam, title, content ) {
				return title && content;
			},
			function( promise ) {
				promise
					.done( function( output ) {
						$form = $(this).closest( '.flow-newtopic-form' );

						var $newRegion = $( output.rendered )
							.hide()
							.insertAfter( $form );

						mw.flow.discussion.setupRegion( $newRegion );

						$newRegion.slideDown();
					} );
			}
		);

		// Overload 'reply' handler
		mw.flow.discussion.setupFormHandler(
			$container,
			'.flow-reply-submit',
			mw.flow.api.reply,
			function() {
				$form = $(this).closest( '.flow-reply-form' );

				var workflowId = $(this)
					.closest( '.flow-topic-container' )
					.data( 'topic-id' );

				var replyToId = $(this)
					.closest( '.flow-post-container' )
					.data( 'post-id' );

				var content = $form.find( '.flow-reply-content' ).val();

				return [ workflowId, replyToId, content ];
			},
			function( workflowId, replyTo, content ) {
				return content;
			},
			function( promise ) {
				promise
					.done( function( output ) {
						var $replyContainer = $(this)
							.closest( '.flow-post-container' )
							.children( '.flow-post-replies' );

						var $newRegion = $( output.rendered )
							.hide()
							.prependTo( $replyContainer );

						mw.flow.discussion.setupRegion( $newRegion );

						$newRegion.slideDown();
					} );
			}
		);

		// Overload 'edit title' link.
		$( '.flow-action-edit-title a' )
			.click( function(e) {
				e.preventDefault();

				var $topicContainer = $(this).closest( '.flow-topic-container' );
				var $titleBar = $topicContainer.find( '.flow-topic-title' );
				var oldTitle = $topicContainer.data( 'title' );

				$titleBar.find( '.flow-realtitle' )
					.hide();

				var $titleEditForm = $( '<form />' )
					.addClass( 'flow-edit-title-form' )
					.append(
						$('<input />')
							.addClass( 'flow-edit-title-textbox' )
							.attr( 'type', 'text' )
							.val( oldTitle )
					)
					.append(
						$('<a/>')
							.addClass( 'flow-cancel-link' )
							.addClass( 'mw-ui-destructive' )
							.attr( 'href', '#' )
							.click( function(e) {
								e.preventDefault();
								$titleBar.children( 'form' )
									.remove();
								$titleBar.find( '.flow-realtitle' )
									.show();
							} )
					)
					.append(
						$( '<input />' )
							.addClass( 'flow-edit-title-submit' )
							.addClass( 'mw-ui-button' )
							.addClass( 'mw-ui-primary' )
							.attr( 'type', 'submit' )
							.val( mw.msg('flow-edit-title-submit' ) )
					)
					.appendTo( $titleBar );

					mw.flow.discussion.setupFormHandler(
						$container,
						'.flow-edit-title-submit',
						mw.flow.api.changeTitle,
						function() {
							workflowId = $topicContainer.data( 'topic-id' );
							content = $titleEditForm.find( '.flow-edit-title-textbox' ).val();

							return [ workflowId, content ];
						},
						function( workflowId, content ) {
							return content && true;
						},
						function( promise ) {
							promise.done(function( output ) {
								$titleBar.find( '.flow-realtitle' )
									.empty()
									.text( output.rendered )
									.show();
							} );
						} );
			} );
	},

	'handleError' : function( $errorDiv, errorArgs ) {
		var apiExceptionPrefix = 'internal_api_error';
		if ( errorArgs[0] == 'http' ) {
			// HTTP error occurred
			$errorDiv.html( mw.message( 'flow-error-http' ).text() );
		} else if ( errorArgs[0].substr( 0, apiExceptionPrefix.length ) == apiExceptionPrefix ) {
			$errorDiv.html( mw.message( 'flow-error-external', errorArgs[1].error.info ).text() );
			console.dir( errorArgs[1] );
		} else if ( errorArgs[0] == 'block-errors' ) {
			var errors = [];
			$.each( errorArgs[1], function( block, blockErrors ) {
				$.each( blockErrors, function( field, errorMsg ) {
					errors.push( errorMsg );
				} );
			} );

			if ( errors.length == 1 ) {
				$errorDiv.html( mw.message( 'flow-error-external', errors.pop() ).text() );
			} else if ( errors.length > 1 ) {
				var $errorList = $( '<ul/>' );
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
	}
};
$( function() {
	mw.flow.discussion.setupRegion( $( '.flow-container' ) );

	$('body')
		.click( function(e) {
			if ( $(e.target)
				.closest('.flow-post-actions, .flow-topic-actions' )
				.length === 0
			) {
				$( '.flow-post-actionbox, .flow-topic-actionbox, .flow-actionbox-pokey' )
					.fadeOut( 'fast' );
			}
		} );
});
} )( jQuery, mediaWiki );