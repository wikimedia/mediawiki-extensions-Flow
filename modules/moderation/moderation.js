( function( $, mw ) {
	// Add function to show moderation dialog
	$.fn._flow.showModerationDialog = function( moderationType ) {
		var $postContainer = $( this ).closest( '.flow-post-container' ),
			$topicContainer = $( this ).closest( '.flow-topic-container' ),
			subject = $topicContainer.data( 'title' ),
			user = $postContainer.data( 'creator-name' ),
			$dialog = $( '<div />' ),
			$form = $( '<form/>' );
			$dialog
				.addClass( 'flow-moderation-dialog' )
				.dialog( {
					'title' : mw.msg( 'flow-moderation-title-'+moderationType ),
					'modal' : true,
					'buttons' : [
						{
							'text' : mw.msg( 'flow-moderation-confirm' ),
							'click' : $(this).flow( 'getFormHandler',
								mw.flow.api.moderatePost,
								function() {
									return [
										$topicContainer.data( 'topic-id' ),
										$postContainer.data( 'post-id' ),
										moderationType,
										$form.find( '#flow-moderation-reason' ).val()
									];
								},
								undefined,
								function( promise ) {
									promise.done( function( output ) {
											var confirmationMsg = 'flow-moderation-confirmation';
											if ( moderationType === 'restore' ) {
												confirmationMsg = 'flow-moderation-confirmation-restore';
											}
											$dialog.empty()
												.append(
													$( '<p/>' )
														.text( mw.msg( confirmationMsg, user ) )
												)
												.dialog( 'option', 'buttons', null );
											var $newContainer = $( output.rendered );
											$postContainer.replaceWith( $newContainer );
											$newContainer.trigger( 'flow_init' );
										} )
										.fail( function() {
											var $errorDiv = $( '<div/>' )
												.flow( 'showError', arguments );
											$form.append( $errorDiv );
										} );
								}
							)
						}
					]
				} );
			$form
				.append(
					$( '<p/>' )
						.text( mw.msg( 'flow-moderation-intro-'+moderationType, user, subject ) )
				)
				.append(
					$( '<p/>' )
						.append(
							$( 'label' )
								.attr( 'for', 'flow-moderation-reason' )
								.text( mw.msg( 'flow-moderation-reason' ) )
						)
				)
				.append(
					$( '<textarea/>' )
						.attr( 'id', 'flow-moderation-reason' )
						.attr( 'placeholder', mw.msg( 'flow-moderation-reason-placeholder' ) )
				)
				.appendTo( $dialog );
	};
} )( jQuery, mediaWiki );