( function ( window, document, $, mw, gt ) {
	var tour = new gt.TourBuilder( {
		name: 'flowOptIn'
	} );

	tour.firstStep( {
		name: 'newTopic',
		titlemsg: 'flow-guidedtour-optin-welcome',
		descriptionmsg: 'flow-guidedtour-optin-welcome-description',
		attachTo: '.flow-newtopic-form [name="topiclist_topic"]',
		position: 'bottom',
		autoFocus: true,
		closeOnClickOutside: false
	} )
	.next( 'findOldConversations' );

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

	tour.step( {
		name: 'feedback',
		titlemsg: 'flow-guidedtour-optin-feedback',
		descriptionmsg: 'flow-guidedtour-optin-feedback-description',
		attachTo: '#pt-preferences',
		position: 'bottom',
		autoFocus: true,
		closeOnClickOutside: false
	} )
	.back( 'findOldConversations' );

}( window, document, jQuery, mediaWiki, mediaWiki.guidedTour ) );
