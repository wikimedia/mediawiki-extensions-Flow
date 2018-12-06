( function () {
	var tour = new mw.guidedTour.TourBuilder( {
		name: 'flowTopic',
		shouldLog: false,
		isSinglePage: true
	} );

	tour.firstStep( {
		name: 'welcome',
		titlemsg: 'flow-guidedtour-topic-welcome',
		descriptionmsg: 'flow-guidedtour-topic-welcome-description',
		position: 'center',
		autoFocus: true,
		overlay: true,
		closeOnClickOutside: true,
		buttons: [
			{
				namemsg: 'flow-skip-summary',
				type: 'secondary',
				action: 'end'
			},
			{
				namemsg: 'flow-guidedtour-discover',
				action: 'next'
			}
		]
	} )
		.next ( 'conversation' )

	tour.step( {
		name: 'conversation',
		titlemsg: 'flow-guidedtour-topic-conversation',
		descriptionmsg: 'flow-guidedtour-topic-conversation-description',
		attachTo: 'h2.flow-topic-title[data-flow-load-handler="topicTitle"]',
		position: 'top',
		autoFocus: true,
		closeOnClickOutside: false,
		buttons: [ {
			namemsg: 'flow-guidedtour-topic-how-to-reply',
			action: 'next'
		} ]
	} )
		.back( 'welcome' )
		.next( 'reply' );

	tour.step( {
		name: 'reply',
		titlemsg: 'flow-guidedtour-topic-reply',
		descriptionmsg: 'flow-guidedtour-topic-reply-description',
		attachTo: 'a.flow-ui-input-replacement-anchor.mw-ui-input',
		position: 'bottom',
		autoFocus: true,
		closeOnClickOutside: false
	} )
		.back( 'conversation' )
		.next( 'directReply' )

	tour.step( {
		name: 'directReply',
		titlemsg: 'flow-guidedtour-topic-direct-reply',
		descriptionmsg: 'flow-guidedtour-topic-reply-description',
		attachTo: 'a.mw-ui-anchor.mw-ui-progressive.mw-ui-quiet.flow-reply-link',
		position: 'top'
	})

}() );
