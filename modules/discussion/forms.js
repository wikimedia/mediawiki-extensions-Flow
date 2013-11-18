( function ( $, mw ) {
$( document ).flow( 'registerInitFunction', function(e) {
	var $container = $( e.target );
	$container.find( 'form.flow-reply-form' ).flow( 'setupEmptyDisabler',
		['.flow-reply-content'],
		'.flow-reply-submit'
	);

	$( 'form.flow-newtopic-form' ).flow( 'setupEmptyDisabler',
		[ '.flow-newtopic-title' ],
		'.flow-newtopic-submit'
	);

	$container.find( 'form.flow-topic-reply-form' ).flow( 'setupEmptyDisabler',
		[ '.flow-topic-reply-content' ],
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
			return !!title;
		},
		function ( promise ) {
			promise
				.done( function ( output ) {
					$( output.rendered )
						.hide()
						.prependTo( $( '.flow-topics' ) )
						.trigger( 'flow_init' )
						.slideDown();
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
				replyToId = $form.find( 'input[name="topic[replyTo]"]' ).val(),
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

					$newRegion.scrollIntoView();
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
					$( output.rendered )
						.hide()
						.insertBefore( $( this ).closest( '.flow-topic-reply-container' ) )
						.trigger( 'flow_init' )
						.slideDown();
					// Reset the form
					// @Todo - this works but doesn't seem right
					var $form = $( this ).closest( '.flow-topic-reply-form' );
					mw.flow.editor.destroy( $form.find( '.flow-topic-reply-content' ) );
					mw.flow.editor.load( $form.find( '.flow-topic-reply-content' ) );
					$form.find( '.flow-post-form-controls' ).hide();
				} );
		}
	);

	function editPostLink( e ) {
			e.preventDefault();
			var $post = $( this ).closest( '.flow-post' ),
				$contentContainer = $post.find( '.flow-post-content' ),
				workflowId = $( this ).flow( 'getTopicWorkflowId' ),
				pageName = $( this ).closest( '.flow-container' ).data( 'page-title' ),
				postId = $post.closest( '.flow-post-container' ).data( 'post-id' );

			if ( $post.find( '.flow-edit-post-form' ).length ) {
				return;
			}

			mw.flow.api.readTopic(
				pageName,
				workflowId,
				{
					'topic' : {
						'no-children' : true,
						'postId' : postId,
						'contentFormat' : mw.flow.editor.getFormat()
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
						{
							'content' : data[0].content['*'],
							'format' : data[0].content.format
						},
						function( content ) {
							return mw.flow.api.editPost( workflowId, postId, content );
						}
					).done( function ( output ) {
						$post
							.empty()
							.append(
								$( output.rendered )
									.find( '.flow-post' )
									.children().click( editPostLink )
							);
					} );

				$post.addClass( 'flow-post-nocontrols' );

				$contentContainer.siblings( 'form' )
					.find( '.flow-cancel-link' )
					.click( function(e) {
						$post
							.removeClass( 'flow-post-nocontrols' );
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
	}
	// Overload 'edit post' link.
	$container.find( '.flow-edit-post-link' )
		.click( editPostLink );

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
						.byteLimit( mw.config.get( 'wgFlowMaxTopicLength' ) )
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
									$titleEditForm.flow( 'hidePreview' );
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
				$titleEditForm.flow( 'setupPreview', { '.flow-edit-title-textbox': 'plain' } );
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
