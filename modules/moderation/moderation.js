( function( $, mw ) {
	/**
	 * Shows a moderation confirmation dialog for the given moderation type, in a given context.
	 * The this variable must point to an element somewhere within the flow-post-container
	 * of the post to moderate.
	 *
	 * Currently, uses jQuery.ui.dialog, but may use a different framework in the future.
	 *
	 * @param {string} moderationType for messages and moderatePost API call. 'restore', 'hide', .. etc.
	 */
	$.fn._flow.showModerationDialog = function( moderationType ) {
		var $postContainer = $( this ).closest( '.flow-post-container' ),
			$topicContainer = $( this ).closest( '.flow-topic-container' ),
			subject = $topicContainer.data( 'title' ),
			$dialog = $( '<div />' ),
			$form = $( '<form/>' ),
			targetType, apiCallback, $resultContainer, creatorName, creatorGender;

		if ( $postContainer.length === 0 ) {
			// moderating a topic
			apiCallback = mw.flow.api.moderateTopic;
			$resultContainer = $topicContainer;
			targetType = 'topic';
		} else {
			apiCallback = mw.flow.api.moderatePost;
			$resultContainer = $postContainer;
			targetType = 'post';
		}
		creatorName = $resultContainer.data( 'creator-name' );
		creatorGender = $resultContainer.data( 'creator-gender' );

		$dialog
			.addClass( 'flow-moderation-dialog' )
			.dialog( {
				// Messages used here:
				// flow-moderation-title-suppress-post
				// flow-moderation-title-delete-post
				// flow-moderation-title-hide-post
				// flow-moderation-title-restore-post
				// flow-moderation-title-suppress-topic
				// flow-moderation-title-delete-topic
				// flow-moderation-title-hide-topic
				// flow-moderation-title-restore-topic
				'title' : mw.msg( 'flow-moderation-title-' + moderationType + '-' + targetType ),
				'modal' : true,
				'buttons' : [
					{
						// Messages used here:
						// flow-moderation-confirm-suppress-post
						// flow-moderation-confirm-delete-post
						// flow-moderation-confirm-hide-post
						// flow-moderation-confirm-restore-post
						// flow-moderation-confirm-suppress-topic
						// flow-moderation-confirm-delete-topic
						// flow-moderation-confirm-hide-topic
						// flow-moderation-confirm-restore-topic
						'text' : mw.msg( 'flow-moderation-confirm-' + moderationType + '-' + targetType ),
						'click' : $(this).flow( 'getFormHandler',
							apiCallback,
							function() {
								var res = [ $topicContainer.data( 'topic-id' ) ];

								if ( $postContainer.length > 0 ) {
									res.push( $postContainer.data( 'post-id' ) );
								}
								res.push( moderationType, $form.find( '#flow-moderation-reason' ).val() );

								return res;
							},
							undefined,
							function( promise ) {
								promise.done( function( output ) {
										var confirmationMsg,
											$newContainer = $( output.rendered );

										// Messages used here:
										// flow-moderation-confirmation-suppress-post
										// flow-moderation-confirmation-delete-post
										// flow-moderation-confirmation-hide-post
										// flow-moderation-confirmation-restore-post
										// flow-moderation-confirmation-suppress-topic
										// flow-moderation-confirmation-delete-topic
										// flow-moderation-confirmation-hide-topic
										// flow-moderation-confirmation-restore-topic
										confirmationMsg = 'flow-moderation-confirmation-' +
											moderationType + '-' + targetType;

										$dialog.empty()
											.dialog( 'option', 'buttons', null )
											.append(
												$( '<p/>' ).text( mw.msg(
													confirmationMsg,
													creatorName,
													mw.user
												) )
											);

										$resultContainer.replaceWith( $newContainer );
										$newContainer.trigger( 'flow_init' );
									} )
									.fail( function() {
										var $errorDiv = $( '<div/>' ).flow( 'showError', arguments ),
											$errors = $form.children( '.flow-error' );

										if ( $errors.length ) {
											$errors.replaceWith( $errorDiv );
										} else {
											$form.append( $errorDiv );
										}
									} );
							}
						)
					}
				]
			} );

		$form
			.append(
				$( '<p/>' )
					.append(
						$( 'label' )
							.attr( 'for', 'flow-moderation-reason' )
							.text(
								mw.message(
									// Messages used here:
									// flow-moderation-intro-suppress-topic
									// flow-moderation-intro-delete-topic
									// flow-moderation-intro-hide-topic
									// flow-moderation-intro-restore-topic
									// flow-moderation-intro-suppress-post
									// flow-moderation-intro-delete-post
									// flow-moderation-intro-hide-post
									// flow-moderation-intro-restore-post
									'flow-moderation-intro-' + moderationType + '-' + targetType,
									creatorName, // $1
									subject, // $2 now unused
									mw.user // $3
								).text()
							)
					)
			)
			.append(
				$( '<textarea/>' )
					.attr( 'id', 'flow-moderation-reason' )
					.byteLimit( 255 )
					.attr( 'placeholder', mw.msg( 'flow-moderation-reason-placeholder' ) )
			)
			.appendTo( $dialog );
	};
} )( jQuery, mediaWiki );
