( function( mw, $ ) {
	var isAdjusted = /\/newer$/;
	function adjustFlowNotificationLinks( $container ) {
		$container.find( 'a[href*="#flow-post-"]' ).each( function ( index ) {
			var $this = $( this ),
				href = $this.attr( 'href' );
			if ( !isAdjusted.test( href ) ) {
				$this.attr( 'href', href + '/newer' );
			}
		} );
	}

	mw.hook( 'ext.echo.overlay.beforeShowingOverlay' ).add( function ( $overlay ) {
		adjustFlowNotificationLinks( $overlay );
	} );

	mw.hook( 'ext.echo.special.onLoadMore' ).add( function ( $li, data ) {
		adjustFlowNotificationLinks( $li );
	} );

	if ( mw.echo && mw.echo.special && mw.echo.special.isInitialized() ) {
		adjustFlowNotificationLinks( $( '#mw-echo-special-container' ) );
	} else {
		mw.hook( 'ext.echo.special.onInitialize' ).add( function () {
			adjustFlowNotificationLinks( $( '#mw-echo-special-container' ) );
		} );
	}
} )( mediaWiki, jQuery );
