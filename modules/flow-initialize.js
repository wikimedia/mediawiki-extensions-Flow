/*!
 * Runs Flow code, using methods in FlowUI.
 */

( function ( $ ) {
	// Pretend we got some data and run with it
	/*
	 * Now do stuff
	 * @todo not like this
	 */
	$( document ).ready( function () {
		var navWidget;

		mw.flow.initComponent( $( '.flow-component' ) );

		// Load data model
		mw.flow.system = new mw.flow.dm.System( {
			pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			tocPostsLimit: 20,
			boardId: $( '.flow-component' ).data( 'flow-id' )
		} );
		mw.flow.system.populateBoardFromApi();

		navWidget = new mw.flow.ui.NavigationWidget( mw.flow.system );

		$( '.flow-board-navigation' ).append( navWidget.$element );
		navWidget.initialize();

		// HACK: These event handlers should be in the prospective widgets
		// they will move once we have Board UI and Topic UI widgets
		mw.flow.system.on( 'resetBoardStart', function () {
			$( '.flow-component' ).addClass( 'flow-api-inprogress' );
		} );
		mw.flow.system.on( 'resetBoardEnd', function ( data ) {
debugger;
			var $rendered = $(
				mw.flow.TemplateEngine.processTemplateGetFragment(
					'flow_block_loop',
					{ blocks: data }
				)
			).children();
			// Run this on a short timeout so that the other board handler in FlowBoardComponentLoadMoreFeatureMixin can run
			// TODO: Using a timeout doesn't seem like the right way to do this.
			setTimeout( function () {
				// Reinitialize the whole board with these nodes
//				flowBoard.reinitializeContainer( $rendered );
				$( '.flow-board' ).empty().append( $rendered );
				$( '.flow-component' ).removeClass( 'flow-api-inprogress' );

				mw.flow.initComponent( $( '.flow-component' ) );
			}, 50 );

		} );
	} );
}( jQuery ) );
