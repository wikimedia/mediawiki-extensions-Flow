( function ( $, mw ) {
	var FlowBoardComponent = mw.flow._FlowBoardComponent;
	/**
	 * Shows a tooltip telling the user that they have subscribed
	 * to this topic|board
	 * @param  {jQuery} $tooltipTarget Element to attach tooltip to.
	 * @param  {string} type           'topic' or 'board'
	 * @param  {string} dir            Direction to point the pointer. 'left' or 'up'
	 */
	FlowBoardComponent.UI.showSubscribedTooltip = function( $tooltipTarget, type, dir ) {
		dir = dir || 'left';

		mw.tooltip.show(
			$tooltipTarget,
			// tooltipTarget will not always be part of a FlowBoardComponent
			$( mw.flow.TemplateEngine.processTemplateGetFragment(
					'flow_tooltip_subscribed',
					{
						unsubscribe: false,
						type: type,
						direction: dir,
						username: mw.user.getName()
					}
				)
			).children(),
			{
				tooltipPointing: dir
			}
		);

		// Hide after 5s
		setTimeout( function () {
			mw.tooltip.hide( $tooltipTarget );
		}, 5000 );
	};
}( jQuery, mediaWiki ) );
