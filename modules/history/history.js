( function ( $, mw ) {
	// Bundled
	$( '.flow-history-bundle' )
	.click( function () {
		var $bundled = $( this ).find( 'ul' );

		if ( $bundled.is( ':visible' ) ) {
			$bundled.hide();
			$( this ).addClass( 'flow-history-bundle-inactive' );
		} else {
			$bundled.show();
			$( this ).removeClass( 'flow-history-bundle-inactive' );
		}
	} )
	// hide bundled records by default
	.addClass( 'flow-history-bundle-inactive' )
	.find( 'ul' ).hide();
} )( jQuery, mediaWiki );
