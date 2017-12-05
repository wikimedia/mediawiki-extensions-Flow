( function ( $ ) {
	// Temporarily here for the experiment
	mw.flowExperimental = mw.flowExperimental || { ui: {}, storage: {} };
	/**
	 * First load initialization, take actions done on the interface
	 * and build the response as lazy-loaded modules
	 */
	$( document ).ready( function () {
		var widgetDone = [],
			lazyLoad = function ( widget, action, id ) {
				var oouiWidget,
					$widget = $( '.mw-flow-container' ).find( '.mw-flow-ui-postWidget.mw-flow-identifier-' + widget + '-' + id );
debugger;
				console.log( '> Lazy loading action "' + action + '" for "' + widget + '" with id: ' + id, $widget );
				// Post widget example
				if ( widget === 'post' ) {
					return mw.loader.using( [ 'ext.flow.ooui.experimental.lazy' ] ).then( function () {
						oouiWidget = new mw.flowExperimental.ui.PostWidget( id, {
							// Send the full element for processing
							$element: $widget,
							topicID: $widget.prop( 'data-topicID' )
						} );
						mw.flowExperimental.storage.post = mw.flowExperimental.storage.post || {};
						mw.flowExperimental.storage.post[ id ] = oouiWidget;
						$widget.replaceWith( oouiWidget.$element );
						return oouiWidget;
					} );
				}

				return $.Deferred().reject().promise();
			};

		// Set up event listeners
		$( '.mw-flow-container a' ).each( function () {
			var data = $( this ).data( 'widget' );

			if ( data ) {
				$( this ).click( function () {
					lazyLoad( data.widget, data.action, data.id )
						.then( function ( widgetObj ) {
							widgetObj.triggerAction( data.action );
						} );
					return false;
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
				widgetDone.push( logString );
				lazyLoad( data.widget, data.action, data.id )
					.done( function ( widgetObj ) {
						debugger;
						widgetObj.triggerAction( data.action );
						console.log( 'triggering action', data.action );
					} );
			}
		} );
		console.log( '> Finished initialization', mwSDInitActions );
		window.mwSDInitActions = [];
	} );
}( jQuery ) );
