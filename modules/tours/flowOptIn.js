( function ( window, document, $, mw, gt ) {
	// TODO: Change the link selector when a class is available on it
	// See phabricator task https://phabricator.wikimedia.org/T105421#1566274
	var archiveLinkExists = $( '.flow-board-header-content p a' ).length,
		tour = new gt.TourBuilder( {
			name: 'flowOptIn',
			isSinglePage: true
		} ),
		$firstStepDescription = $( '<div>' )
			.addClass( 'flow-guidedtour-optin-firststep-description' )
			.append(
				$( '<div>' )
					.addClass( 'flow-guidedtour-optin-firststep-description-image' ),
				mw.msg( 'flow-guidedtour-optin-welcome-description' )
			);

	tour.firstStep( {
		name: 'newTopic',
		titlemsg: 'flow-guidedtour-optin-welcome',
		description: $firstStepDescription[0].outerHTML,
		attachTo: '.flow-newtopic-form [name="topiclist_topic"]',
		position: 'bottom',
		autoFocus: true,
		closeOnClickOutside: false
	} )
	.next( archiveLinkExists ? 'findOldConversations' : 'feedback' );

	if ( archiveLinkExists ) {
		tour.step( {
			name: 'findOldConversations',
			titlemsg: 'flow-guidedtour-optin-find-old-conversations',
			descriptionmsg: 'flow-guidedtour-optin-find-old-conversations-description',
			attachTo: '.flow-board-header-content',
			position: 'bottom',
			autoFocus: true,
			closeOnClickOutside: false
		} )
		.next( 'feedback' )
		.back( 'newTopic' );
	}

	tour.step( {
		name: 'feedback',
		titlemsg: 'flow-guidedtour-optin-feedback',
		descriptionmsg: 'flow-guidedtour-optin-feedback-description',
		attachTo: '#pt-betafeatures',
		position: 'bottom',
		autoFocus: true,
		closeOnClickOutside: false
	} )
	.back( archiveLinkExists ? 'findOldConversations' : 'feedback' );

}( window, document, jQuery, mediaWiki, mediaWiki.guidedTour ) );
