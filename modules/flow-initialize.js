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
		mw.flow.Initialize( $( '.flow-component' ) );
	} );
}( jQuery ) );
