( function ( $, mw, M ) {
	var FlowBoardComponent = mw.flow._FlowBoardComponent,
		toast = M.require( 'toast' );
	/**
	 * Shows a tooltip telling the user that they have subscribed
	 * to this topic|board
	 * @param  {jQuery} $tooltipTarget Element to attach tooltip to.
	 * @param  {string} type           'topic' or 'board'
	 * @param  {string} dir            Direction to point the pointer. 'left' or 'up'
	 */
	FlowBoardComponent.UI.showSubscribedTooltip = function( $tooltipTarget, type, dir ) {
		toast.show( mw.msg( 'flow-' + type + '-notification-subscribe-title', mw.user.getName() ) );
	};
}( jQuery, mediaWiki, mediaWiki.mobileFrontend ) );
