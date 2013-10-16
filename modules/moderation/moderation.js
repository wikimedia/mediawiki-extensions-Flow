( function( $, mw ) {
	// Add function to show moderation dialo
	$.fn._flow.showModerationDialog = function( moderationType ) {
		var $postContainer = $( this ).closest( '.flow-post-container' ),
			$topicContainer = $( this ).closest( '.flow-topic-container' ),
			subject = $topicContainer.data( 'title' ),
			user = $postContainer.data( 'creator-name' ),
			dialog = $( '<div />' )
				.addClass( 'flow-moderation-dialog' )
				.dialog( {
					'title' : mw.msg( 'flow-moderation-title-'+moderationType ),
					'modal' : true,
					'buttons' : [
						{
							'text' : mw.msg( 'flow-moderation-confirm' )
						}
					]
				} )
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
				);
	};
} )( jQuery, mediaWiki );