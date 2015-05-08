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
	} );
}( jQuery ) );
