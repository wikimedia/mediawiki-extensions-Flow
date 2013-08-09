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
		$container.find( '.flow-newtopic-submit' )
			.click( function(e) {
				e.preventDefault();
				var $button = $( this );
				var $form = $button.closest( '.flow-newtopic-form' );

				$form.find( '.flow-error' )
					.remove();

				var topicTitle = $container.find( '.flow-newtopic-title' ).val();
				var content = $container.find( '.flow-newtopic-content' ).val();

				if ( ! topicTitle || ! content ) {
					$(this).attr( 'disabled', 'disabled' );
					return;
				}

				$(this).hide();
				var $spinner = $( '<div/>' )
					.addClass( 'flow-loading-indicator' )
					.insertAfter( $(this) );

				var workflowParam = mw.flow.discussion.getWorkflowParameters( $container );

				mw.flow.api.newTopic( workflowParam, topicTitle, content )
					.done( function(output) {
						$spinner.remove();
						$button.show();
						$form.find( '.flow-cancel-link' )
							.click();
						
						var $newRegion = $( output.rendered )
							.hide()
							.insertAfter( $form );

						mw.flow.discussion.setupRegion( $newRegion );

						$newRegion.slideDown();
					} )
					.fail( function( ) {
						$spinner.remove();
						$button.show();
						var $errorDiv = $( '<div/>' )
							.addClass( 'flow-error' )
							.insertAfter( $form.find( '.flow-post-form-controls' ) )
							.hide()
							.slideDown( 'fast' );

						mw.flow.discussion.handleError( $errorDiv, arguments );
					} );
			} );

		// Overload 'reply' handler.
		$container.find( '.flow-reply-submit' )
			.click( function(e) {
				e.preventDefault();
				var $button = $( this );
				var $replyForm = $( this ).closest( '.flow-reply-form' );
				var $postContainer = $( this ).closest( '.flow-post-container' );
				var $topicContainer = $( this ).closest( '.flow-topic-container' );

				$replyForm.find( '.flow-error' )
					.remove();

				var workflowId = $topicContainer.data( 'topic-id' );
				var pageName = $container.data( 'page-title' );

				var content = $replyForm.find( '.flow-reply-content' ).val();
				var replyTo = $postContainer.data( 'post-id' );

				if (  ! content ) {
					$button.attr( 'disabled', 'disabled' );
					return;
				}

				$button.hide();
				var $spinner = $( '<div/>' )
					.addClass( 'flow-loading-indicator' )
					.insertAfter( $(this) );

				mw.flow.api.reply( workflowId, replyTo, content )
					.done( function(output) {
						$spinner.remove();
						$button.show();
						$replyForm.find( '.flow-cancel-link' )
							.click();
						var $replyContainer = $postContainer.children( '.flow-post-replies' );

						var $newRegion = $( output.rendered )
							.hide()
							.prependTo( $replyContainer );

						mw.flow.discussion.setupRegion( $newRegion );

						$newRegion.slideDown();
					} )
					.fail( function( ) {
						$spinner.remove();
						$button.show();
						var $errorDiv = $( '<div/>' )
							.addClass( 'flow-error' )
							.insertAfter( $replyForm.find( '.flow-post-form-controls' ) )
							.hide()
							.slideDown( 'fast' );
						mw.flow.discussion.handleError( $errorDiv, arguments );
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