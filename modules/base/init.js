( function ( $, mw ) {
	$( function () {
		$( '.flow-container' ).trigger( 'flow_init' );
	} );

	$( document ).on( 'flow_init', function( e ) {
		$( e.target ).addClass( 'flow-initialised' );
		mw.hook( 'wikipage.content' ).fire( $( e.target ) );
	} );
} )( jQuery, mediaWiki );
