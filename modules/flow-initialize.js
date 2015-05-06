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
		mw.flow.initComponent( $( '.flow-component' ) );

		// Load data model
		mw.flow.system = new mw.flow.dm.System( {
			pageTitle: mw.Title.newFromText( mw.config.get( 'wgPageName' ) ),
			tocPostLimit: 20,
			boardId: $( '.flow-component' ).data( 'flow-id' )
//			highlight: String( window.location.hash.match( /[0-9a-z]{16,19}$/i ) || '' )
		} );

		mw.flow.system.populateBoardFromApi();
	} );
}( jQuery ) );
