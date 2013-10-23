( function( $, mw ) {
$( function() {
	/**
	 * Creates an event handler
	 * @param  {Function} callback Function that takes the currently
	 * highlighted element as a parameter and returns the next element to
	 * highlight.
	 * @return {Function}          Event handler
	 */
	var createNavigationHandler = function( callback ) {
		return function(e) {
			var $current = $( '.flow-post-highlighted' );
			var $next;

			if ( $current.length === 0 ) {
				$next = $( '.flow-post-container:first' );
			} else {
				$next = callback( $current );
			}

			$current.removeClass( 'flow-post-highlighted' );

			if ( ! $next.length ) {
				console.log( "No next element" );
				return;
			}

			$next.addClass( 'flow-post-highlighted' );

			$viewport = $( 'main, html' );
			$(window).scrollTop( $next.position().top - ( $viewport.height() / 2 ) );
			window.location.hash = '#' + $next.find( '.flow-post' ).attr( 'id' );
		};
	};

	$( document ).bind( 'keypress', 'j', createNavigationHandler( function( $cur ) {
		var $allPosts = $( '.flow-post-container' );
		var index = $allPosts.index( $cur );

		if ( index + 1 >= $allPosts.length ) {
			return $cur;
		}

		return $( $allPosts.get( index + 1 ) );
	} ) );

	$( document ).bind( 'keypress', 'k', createNavigationHandler( function( $cur ) {
		var $allPosts = $( '.flow-post-container' );
		var index = $allPosts.index( $cur );

		if ( index - 1 < 0 ) {
			return $cur;
		}

		return $( $allPosts.get( index - 1 ) );
	} ) );
} );
} )( jQuery, mediaWiki );