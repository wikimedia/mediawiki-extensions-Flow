( function ( $ ) {
	// Temporarily here for the experiment
	mw.flowExperimental = mw.flowExperimental || { ui: {} };
	/**
	 * First load initialization, take actions done on the interface
	 * and build the response as lazy-loaded modules
	 */
	$( document ).ready( function () {
		var widgetDone = [],
			lazyLoad = function ( widget, action, id ) {
			console.log( 'lazy-load action "' + action + '" for "' + widget + '" with id: ' + id );
		};

		// Set up event listeners
		$( '.mw-flow-container a' ).each( function () {
			var data = $( this ).data( 'widget' );

			if ( data ) {
				$( this ).click( function () {
					lazyLoad( data.widget, data.action, data.id );
				} );
			}
		} );

		// Go over initialization buffer stack
		window.mwSDInitActions = window.mwSDInitActions || [];
		window.mwSDInitActions.forEach( function ( action ) {
			var logString,
				$widget = $( action[ 0 ] ),
				data = $widget.data( 'widget' );

			if ( !data ) {
				return;
			}

			logString = data.widget + '|' + data.action + '|' + data.id;

			// Only do actions per widget once, even if the
			// user clicked multiple times
			if ( widgetDone.indexOf( logString ) === -1 ) {
				lazyLoad( data.widget, data.action, data.id );
				widgetDone.push( logString );
			}
		} );
		console.log( 'initialized', mwSDInitActions );
		window.mwSDInitActions = [];
	} );
}( jQuery ) );
