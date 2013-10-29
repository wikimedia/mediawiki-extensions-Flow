( function ( $, mw ) {
	$( function () {
		$( '.flow-container' ).trigger( 'flow_init' );
	} );

	$( document ).on( 'flow_init', function(e) {
		$( e.target ).addClass( 'flow-initialised ' );
	} );
} )( jQuery, mediaWiki );
