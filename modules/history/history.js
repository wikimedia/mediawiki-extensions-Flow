( function ( $, mw ) {
	// Bundled
	$( '.flow-history-bundle' )
		.click( function ( e ) {
			var $bundled = $( this ).find( 'ul' );

			// when clicking a child li, the bundle should not collapse again
			if ( !$( e.target ).closest( 'li' ).hasClass( 'flow-history-bundle' ) ) {
				return;
			}

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

	$( '.flow-history-log' ).find( '.flow-datestamp' )
		.hover( function () {
			$( this ).find( '.flow-agotime' ).toggle();
		} );
} )( jQuery, mediaWiki );
