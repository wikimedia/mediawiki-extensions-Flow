( function( $, mw ) {
$(document).on( 'flow_init', function( e ) {
	$container = $( e.target );
	$container.find('form.flow-reply-form').flow( 'setupEmptyDisabler',
		['.flow-reply-content'],
		'.flow-reply-submit'
	);

	$('form.flow-newtopic-form').flow( 'setupEmptyDisabler',
		[
			'.flow-newtopic-title',
			'.flow-newtopic-content'
		],
		'.flow-newtopic-submit'
	);

	// Overload 'new topic' handler.
	$container.flow( 'setupFormHandler',
		'.flow-newtopic-submit',
		mw.flow.api.newTopic,
		function() {
			$form = $(this).closest( '.flow-newtopic-form' );

			var workflowParam = $container.flow( 'getWorkflowParameters' );
			var title = $form.find( '.flow-newtopic-title' ).val();
			var content = mw.flow.editor.getContent( $form.find( '.flow-newtopic-content' ) );

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

					$newRegion.trigger( 'flow_init' );

					$newRegion.slideDown();
				} );
		}
	);

	// Overload 'reply' handler
	$container.flow( 'setupFormHandler',
		'.flow-reply-submit',
		mw.flow.api.reply,
		function() {
			$form = $(this).closest( '.flow-reply-form' );

			var workflowId = $( this ).flow( 'getTopicWorkflowId' );

			var replyToId = $( this )
				.closest( '.flow-post-container' )
				.data( 'post-id' );

			var content = mw.flow.editor.getContent( $form.find( '.flow-reply-content' ) );

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

					$newRegion.trigger( 'flow_init' );

					$newRegion.slideDown();
				} );
		}
	);

	// Overload 'edit post' link.
	$container.find( '.flow-action-edit-post a' )
		.click( function(e) {
			e.preventDefault();
			var $postContainer = $(this).closest( '.flow-post' );
			var $contentContainer = $postContainer.find( '.flow-post-content' );
			var workflowId = $( this ).flow( 'getTopicWorkflowId' );
			var pageName = $(this).closest( '.flow-container' ).data( 'page-title' );
			var postId = $postContainer.data( 'post-id' );

			if ( $postContainer.find( '.flow-edit-post-form' ).length ) {
				return;
			}

			mw.flow.api.readTopic(
				pageName,
				workflowId,
				{
					'topic' : {
						'no-children' : true,
						'postId' : postId
					}
				}
			)
				.done( function( data ) {
					if ( ! data[0] || data[0]['post-id'] != postId ) {
						console.dir( data );
						var $errorDiv = $( '<div/>' )
							.addClass( 'flow-error' )
							.text( mw.msg( 'flow-error-other') )
							.hide()
							.insertAfter( $contentContainer )
							.slideDown();
						return;
					}

					var originalContent = data[0]['content-src']['*'];

					var $postForm = $( '<form />' )
						.addClass( 'flow-edit-post-form' );

					$postForm
						.append(
							$( '<textarea />' )
								.val( originalContent )
								.addClass( 'flow-edit-post-content' )
						)
						.append(
							$( '<div/>' )
								.addClass( 'flow-post-controls' )
								.append(
									$( '<a/>' )
										.text( mw.msg( 'flow-cancel' ) )
										.addClass( 'flow-cancel-link' )
										.addClass( 'mw-ui-destructive' )
										.attr( 'href', '#' )
										.click( function(e) {
											e.preventDefault();
											$postForm.slideUp( 'fast',
												function() {
													$contentContainer.show();
													$postForm.remove();
												}
											);
										} )
								)
								.append(
									$( '<input />' )
										.attr( 'type', 'submit' )
										.addClass( 'mw-ui-button' )
										.addClass( 'mw-ui-primary' )
										.addClass( 'flow-edit-post-submit' )
										.val( mw.msg( 'flow-edit-post-submit' ) )
								)
						)
						.insertAfter( $contentContainer );

					$contentContainer.hide();

					$postContainer.flow( 'setupFormHandler',
						'.flow-edit-post-submit',
						mw.flow.api.editPost,
						function() {
							var content = $postForm.find( '.flow-edit-post-content' ).val();

							return [ workflowId, postId, content ];
						},
						function( workflowId, postId, content ) {
							return content;
						},
						function( promise ) {
							promise.done( function(output) {
								$contentContainer
									.empty()
									.append(
										$(output.rendered)
											.find('.flow-post-content')
											.children()
									);
								} );
						}
					);

					$('form.flow-edit-post-form').flow( 'setupEmptyDisabler',
						[
							'.flow-edit-post-content'
						],
						'.flow-edit-post-submit'
					);
				} )
				.fail( function() {
					var $errorDiv = $( '<div/>' )
						.addClass( 'flow-error' )
						.hide();

					$errorDiv.flow( 'showError', arguments );

					$errorDiv.insertAfter( $contentContainer )
						.slideDown();
				} );
		} );

	// Overload 'edit title' link.
	$container.find( '.flow-action-edit-title a' )
		.click( function(e) {
			e.preventDefault();

			var $topicContainer = $(this).closest( '.flow-topic-container' );
			var $titleBar = $topicContainer.find( '.flow-topic-title' );
			var oldTitle = $topicContainer.data( 'title' );

			$titleBar.find( '.flow-realtitle' )
				.hide();

			var $titleEditForm = $( '<form />' );
			$titleEditForm
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
						.text( mw.msg( 'flow-cancel' ) )
						.click( function(e) {
							e.preventDefault();
							$titleBar.children( 'form' )
								.remove();
							$titleBar.find( '.flow-realtitle' )
								.show();
							$titleEditForm.remove();
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
				.appendTo( $titleBar )
				.find( '.flow-edit-title-textbox' )
					.focus()
					.select();

				$titleEditForm.flow( 'setupFormHandler',
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

							$topicContainer.data( 'title', output.rendered );

							$titleEditForm.remove();
						} );
					} );
		} );
} );
} )( jQuery, mediaWiki );