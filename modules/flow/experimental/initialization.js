( function ( $ ) {
	/**
	 * First load initialization, take actions done on the interface
	 * and build the response as lazy-loaded modules
	 */
	$( document ).ready( function () {
		// var progressBar = OO.ui.infuse( $( '.ve-init-mw-collabTarget-loading' ) ),

		// $( '.mw-flow-ui-postWidget-bottomMenu-actions .oo-ui-buttonWidget' ).each( function () {
		// 	var button = OO.ui.infuse( $( this ) );
		// 	console.log( 'infused', button );
		// } );

		// Go over initialization stack
		window.mwSDInitActions = window.mwSDInitActions || [];
		console.log( 'initialized', mwSDInitActions );
	} );
}( jQuery ) );
