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
				namemsg: 'flow-guidedtour-topic-skip',
				type: 'secondary',
				action: 'end'
			},
			{
				namemsg: 'flow-guidedtour-topic-welcome-discover',
				action: 'next'
			}
		]
	} )
		.next( 'conversation' );

	tour.step( {
		name: 'conversation',
		titlemsg: 'flow-guidedtour-topic-conversation',
		descriptionmsg: 'flow-guidedtour-topic-conversation-description',
		attachTo: 'h2.flow-topic-title[data-flow-load-handler="topicTitle"]',
		position: 'top',
		autoFocus: true,
		closeOnClickOutside: false,
		buttons: [ {
			namemsg: 'flow-guidedtour-topic-howToReply',
			action: 'next'
		} ]
	} )
		.back( 'welcome' )
		.next( 'reply' );

	function getReplyAttachTo() {
		var replyAttachTo = 'a.flow-ui-input-replacement-anchor.mw-ui-input';
		if ( $( replyAttachTo ).length === 0 ) {
			replyAttachTo = 'div.flow-ui-replyWidget input.oo-ui-inputWidget-input';
		}
		if ( $( replyAttachTo ).length === 0 ) {
			replyAttachTo = 'div.low-ui-replyWidget-trigger-input input.oo-ui-inputWidget-input';
		}
		if ( $( replyAttachTo ).length === 0 ) {
			replyAttachTo = 'textarea#flow-post-' + mw.config.get( 'wgPageName' ).slice( ( mw.config.get( 'wgCanonicalNamespace' ) + ':' ).length ).toLowerCase() + '-form-content';
		}
		return replyAttachTo;
	}
	tour.step( {
		name: 'reply',
		titlemsg: 'flow-guidedtour-topic-reply',
		descriptionmsg: 'flow-guidedtour-topic-reply-description',
		attachTo: getReplyAttachTo(),
		position: 'bottom',
		autoFocus: true,
		closeOnClickOutside: false,
		buttons: [ {
			namemsg: 'flow-guidedtour-topic-replyToSpecificComment',
			action: 'next'
		} ]
	} )
		.back( 'conversation' )
		.next( 'directReply' );

	tour.step( {
		name: 'directReply',
		titlemsg: 'flow-guidedtour-topic-directReply',
		descriptionmsg: 'flow-guidedtour-topic-directReply-description',
		attachTo: 'a.flow-reply-link',
		position: 'right',
		autoFocus: true,
		buttons: [ {
			namemsg: 'flow-guidedtour-topic-howToWatchTopics',
			action: 'next'
		} ]
	} )
		.back( 'reply' )
		.next( 'watch' );

	tour.step( {
		name: 'watch',
		titlemsg: 'flow-guidedtour-topic-watch',
		descriptionmsg: 'flow-guidedtour-topic-watch-description',
		position: 'left',
		attachTo: 'span.flow-watch',
		autoFocus: true
	} )
		.back( 'directReply' )
		.next( 'goodbye' );

	tour.step( {
		name: 'goodbye',
		titlemsg: 'flow-guidedtour-topic-goodbye',
		descriptionmsg: 'flow-guidedtour-topic-goodbye-description',
		position: 'left',
		attachTo: '#contentSub a',
		autoFocus: true,
		buttons: [ {
			namemsg: 'flow-guidedtour-topic-goodbye-openHelpPage',
			action: 'externalLink',
			type: 'secondary',
			url: 'https://www.mediawiki.org/wiki/Special:MyLanguage/Help:Structured_Discussions'
		} ]
	} )
		.back( 'directReply' );

}() );
