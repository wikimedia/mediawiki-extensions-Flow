( function ( $, mw ) {
$( document ).on( 'flow_init', function ( e ) {
	var $container = $( e.target );
	$container.find( 'form.flow-reply-form' ).flow( 'setupEmptyDisabler',
		['.flow-reply-content'],
		'.flow-reply-submit'
	);

	$( 'form.flow-newtopic-form' ).flow( 'setupEmptyDisabler',
		[
			'.flow-newtopic-title',
			'.flow-newtopic-content'
		],
		'.flow-newtopic-submit'
	);

	$container.find( 'form.flow-topic-reply-form' ).flow( 'setupEmptyDisabler',
		['.flow-topic-reply-content'],
		'.flow-topic-reply-submit'
	);

	// Overload 'new topic' handler.
	$container.flow( 'setupFormHandler',
		'.flow-newtopic-submit',
		mw.flow.api.newTopic,
		function() {
			var $form = $( this ).closest( '.flow-newtopic-form' ),
				workflowParam = $container.flow( 'getWorkflowParameters' ),
				title = $form.find( '.flow-newtopic-title' ).val(),
				content = mw.flow.editor.getContent( $form.find( '.flow-newtopic-content' ) );

			return [ workflowParam, title, content ];
		},
		function ( workflowParam, title, content ) {
			return title && content;
		},
		function ( promise ) {
			promise
				.done( function ( output ) {
					var $form = $( this ).closest( '.flow-newtopic-form' ),
						$newRegion = $( output.rendered )
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
			var $form = $( this ).closest( '.flow-reply-form' ),
				workflowId = $( this ).flow( 'getTopicWorkflowId' ),
				replyToId = $( this )
					.closest( '.flow-post-container' )
					.data( 'post-id' ),
				content = mw.flow.editor.getContent( $form.find( '.flow-reply-content' ) );

			return [ workflowId, replyToId, content ];
		},
		function ( workflowId, replyTo, content ) {
			return content;
		},
		function ( promise ) {
			promise
				.done( function ( output ) {
					var $replyContainer = $( this )
							.closest( '.flow-post-container' )
							.children( '.flow-post-replies' ),
						$newRegion = $( output.rendered )
							.hide()
							.prependTo( $replyContainer );

					$newRegion.trigger( 'flow_init' );

					$newRegion.slideDown();
				} );
		}
	);

	// Overload 'topic reply' handler
	$container.flow( 'setupFormHandler',
		'.flow-topic-reply-submit',
		mw.flow.api.reply,
		function() {
			var $form = $( this ).closest( '.flow-topic-reply-form' ),
				workflowId = $( this ).flow( 'getTopicWorkflowId' ),
				replyToId = $( this )
					.closest( '.flow-topic-reply-container' )
					.data( 'post-id' ),
				content = mw.flow.editor.getContent( $form.find( '.flow-topic-reply-content' ) );

			return [ workflowId, replyToId, content ];
		},
		function ( workflowId, replyTo, content ) {
			return content;
		},
		function ( promise ) {
			promise
				.done( function ( output ) {
					// Replies are currently sorted in timestamp descending order.
					// If we change it to ascending order, then we need to append
					// the new element to the end of the container and remove the
					// sliding-up effect
					var $replyContainer = $( this )
							.closest( '.flow-topic-container' ).children(":first"),
						$newRegion = $( output.rendered )
							.hide()
							.insertAfter( $replyContainer )
							.trigger( 'flow_init' )
							.slideDown();
					$( 'html,body' ).animate( {
						'scrollTop': $( '#flow-topic-' + $( this ).closest( '.flow-topic-container' ).data( 'topic-id' ) ).offset().top
					}, 500 );
					// Reset the form
					// @Todo - this works but doesn't seem right
					var $form = $( this ).closest( '.flow-topic-reply-form' );
					mw.flow.editor.destroy( $form.find( '.flow-topic-reply-content' ) );
					mw.flow.editor.load( $form.find( '.flow-topic-reply-content' ) );
				} );
		}
	);

	// Overload 'edit post' link.
	$container.find( '.flow-edit-post-link' )
		.click( function ( e ) {
			e.preventDefault();
			var $postContainer = $( this ).closest( '.flow-post' ),
				$contentContainer = $postContainer.find( '.flow-post-content' ),
				workflowId = $( this ).flow( 'getTopicWorkflowId' ),
				pageName = $( this ).closest( '.flow-container' ).data( 'page-title' ),
				postId = $postContainer.data( 'post-id' );

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
				.done( function ( data ) {
					if ( !data[0] || data[0]['post-id'] !== postId ) {
						console.dir( data );
						$( '<div/>' )
							.addClass( 'flow-error' )
							.text( mw.msg( 'flow-error-other' ) )
							.hide()
							.insertAfter( $contentContainer )
							.slideDown();
						return;
					}

					var $postForm = $( '<form />' )
						.addClass( 'flow-edit-post-form' );

					$postForm
						.append(
							$( '<textarea />' )
								.addClass( 'flow-edit-post-content' )
						)
						.append(
							$( '<div/>' )
								.addClass( 'flow-post-form-controls' )
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
										.addClass( 'flow-edit-post-submit' )
										.val( mw.msg( 'flow-edit-post-submit' ) )
								)
						)
						.insertAfter( $contentContainer );

					mw.flow.editor.load( $postForm.find( 'textarea' ), data[0].content['*'] );

					$contentContainer.hide();

					$postContainer.flow( 'setupFormHandler',
						'.flow-edit-post-submit',
						mw.flow.api.editPost,
						function () {
							var content = mw.flow.editor.getContent( $postForm.find( '.flow-edit-post-content' ) );
							return [ workflowId, postId, content ];
						},
						function ( workflowId, postId, content ) {
							return content;
						},
						function ( promise ) {
							promise.done( function ( output ) {
								$postContainer
									.empty()
									.append(
										$( output.rendered )
											.find( '.flow-post' )
											.children()
									);
								} );
								$contentContainer = $postContainer.find( '.flow-post-content' );
						}
					);

					$( 'form.flow-edit-post-form' ).flow( 'setupEmptyDisabler',
						[
							'.flow-edit-post-content'
						],
						'.flow-edit-post-submit'
					);
				} )
				.fail( function () {
					var $errorDiv = $( '<div/>' )
						.addClass( 'flow-error' )
						.hide();

					$errorDiv.flow( 'showError', arguments );

					$errorDiv.insertAfter( $contentContainer )
						.slideDown();
				} );
		} );

	// Overload 'edit title' link.
	$container.find( 'a.flow-edit-topic-link' )
		.click( function ( e ) {
			e.preventDefault();

			var $topicContainer = $( this ).closest( '.flow-topic-container' ),
				$titleBar = $topicContainer.find( '.flow-topic-title' ),
				oldTitle = $topicContainer.data( 'title' ),
				$titleEditForm = $( '<form />' ),
				$realTitle = $titleBar.find( '.flow-realtitle' );

			// check if form has not already been opened
			if ( !$realTitle.is( ':visible' ) ) {
				return;
			}

			$realTitle.hide();

			$titleEditForm
				.addClass( 'flow-edit-title-form' )
				.append(
					$( '<input />' )
						.addClass( 'mw-ui-input' )
						.addClass( 'flow-edit-title-textbox' )
						.attr( 'type', 'text' )
						.val( oldTitle )
				)
				.append(
					$( '<div class="flow-edit-title-controls"></div>' )
						.append(
							$( '<a/>' )
								.addClass( 'flow-cancel-link' )
								.addClass( 'mw-ui-button' )
								.addClass( 'mw-ui-text' )
								.attr( 'href', '#' )
								.text( mw.msg( 'flow-cancel' ) )
								.click( function ( e ) {
									e.preventDefault();
									$titleBar.children( 'form' )
										.remove();
									$realTitle
										.show();
									$titleEditForm.remove();
								} )
						)
						.append( ' ' )
						.append(
							$( '<input />' )
								.addClass( 'flow-edit-title-submit' )
								.addClass( 'mw-ui-button' )
								.addClass( 'mw-ui-constructive' )
								.attr( 'type', 'submit' )
								.val( mw.msg( 'flow-edit-title-submit' ) )
						)
				)
				.appendTo( $titleBar )
				.find( '.flow-edit-title-textbox' )
					.focus()
					.select();

				$titleEditForm.flow( 'setupFormHandler',
					'.flow-edit-title-submit',
					mw.flow.api.changeTitle,
					function () {
						var workflowId = $topicContainer.data( 'topic-id' ),
							content = $titleEditForm.find( '.flow-edit-title-textbox' ).val();

						return [ workflowId, content ];
					},
					function ( workflowId, content ) {
						return content && true;
					},
					function ( promise ) {
						promise.done( function ( output ) {
							$realTitle
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
