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
		// $( mw.flow.TemplateEngine.processTemplateGetFragment( 'flow_board', mockData ) ).insertBefore( '#flow_board-partial' );

		mw.flow.initComponent( $( '.flow-component' ) );
	} );
}( jQuery ) );

