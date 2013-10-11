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
							.appendTo( $replyContainer ),
						$mainContainer = $('html,body');

					$newRegion.trigger( 'flow_init' );

					$newRegion.slideDown();

					$mainContainer.animate({
						scrollTop: $newRegion.offset().top - $mainContainer.height() / 2
					});
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


				$contentContainer.flow( 'setupEditForm',
						'post',
						data[0].content['*'],
						function( content ) {
							return mw.flow.api.editPost( workflowId, postId, content );
						}
					).done( function ( output ) {
						$postContainer
							.empty()
							.append(
								$( output.rendered )
									.find( '.flow-post' )
									.children()
							);
					} );
			} )
			.fail( function () {
				var $errorDiv = $( '<div/>' )
					.addClass( 'flow-error' )
					.hide();

				$errorDiv.flow( 'showError', arguments );

				$errorDiv.insertAfter( $contentContainer )
					.slideDown();
			} );
		});

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
