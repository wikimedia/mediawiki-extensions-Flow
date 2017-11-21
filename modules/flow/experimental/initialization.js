( function ( $ ) {
	/**
	 * First load initialization, take actions done on the interface
	 * and build the response as lazy-loaded modules
	 */
	$( document ).ready( function () {
		var lazyLoad = function ( widget, id ) {
			console.log( 'lazy-load reply widget for ' + widget + ' id: ' + id );
		};

		// Set up event listeners
		$( '.mw-flow-ui-postWidget-actions-reply a' ).each( function () {
			var data = $( this ).data( 'widget' );
			$( this ).click( function () {
				lazyLoad( data.type, data.id );
			} );
		} );

		// Go over initialization stack
		mwSDInitActions = mwSDInitActions || [];
		mwSDInitActions.forEach( function ( action ) {
			var $widget = $( action[ 0 ] ),
				data = $widget.data( 'widget' );

			lazyLoad( data.type, data.id );
		} );
		console.log( 'initialized', mwSDInitActions );
	} );
}( jQuery ) );
