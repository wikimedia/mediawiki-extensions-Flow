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
			user = $postContainer.data( 'creator-name' ),
			$dialog = $( '<div />' ),
			$form = $( '<form/>' ),
			apiCallback, successCallback;

			if ( $postContainer.length === 0 ) {
				// moderating a topic
				apiCallback = mw.flow.api.moderateTopic;
				successCallback = function( html ) {
					var $newContainer = $( html );
					$topicContainer.replaceWith( $newContainer );
					$newContainer.trigger( 'flow_init' );
				};
			} else {
				apiCallback = mw.flow.api.moderatePost;
				successCallback = function( html) {
					var $newContainer = $( html );
					$postContainer.replaceWith( $newContainer );
					$newContainer.trigger( 'flow_init' );
				};
			}
			$dialog
				.addClass( 'flow-moderation-dialog' )
				.dialog( {
					'title' : mw.msg( 'flow-moderation-title-'+moderationType ),
					'modal' : true,
					'buttons' : [
						{
							'text' : mw.msg( 'flow-moderation-confirm' ),
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
											var confirmationMsg = 'flow-moderation-confirmation';
											if ( moderationType === 'restore' ) {
												confirmationMsg = 'flow-moderation-confirmation-restore';
											}
											$dialog.empty()
												.dialog( 'option', 'buttons', null )
												.append(
													$( '<p/>' )
														.text( mw.msg( confirmationMsg, user ) )
												);
											successCallback( output.rendered );
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
