/*!
 * This file provides a shim to load Flow when clicking an interactive
 * Flow link.
 */
(function( $, mw ) {
	function clickedFlowLink( event ) {
		var $container = $( event.delegateTarget ),
			onComplete = function() {
				$( event.target ).click();
			};

		event.preventDefault();

		$container.
			addClass( 'flow-component' ).
			data( 'flow-component', 'boardHistory' );

		// if successfull, flow will now handle clicking the target
		// If that failed still run the onComplete, it will not trigger
		// our handler and be a normal click this time.
		mw.loader.using(
			[ 'ext.flow', 'ext.flow.mediawiki.ui.modal', 'mediawiki.ui.input' ],
			onComplete,
			onComplete
		);
	}

	$( document ).ready( function() {
		$( '#bodyContent' ).one( 'click', '.flow-click-interactive', clickedFlowLink );
	} );
} )( jQuery, mediaWiki );
