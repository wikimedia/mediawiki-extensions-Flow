( function ( $, undefined ) {
	/**
	 * Scrolls the viewport to fit $el into view only if necessary. Scenarios:
	 * 1. If el starts above viewport, scrolls to put top of el at top of viewport.
	 * 2. If el ends below viewport and fits into viewport, scrolls to put bottom of el at bottom of viewport.
	 * 3. If el ends below viewport but is taller than the viewport, scrolls to put top of el at top of viewport.
	 * @param {string|int} [speed='fast']
	 */
	$.fn.conditionalScrollIntoView = function ( speed ) {
		speed = speed !== undefined ? speed : 'fast';

		var viewportX = $( window ).scrollTop(),
			viewportHeight = $( window ).height(),
			elOffset = this.offset(),
			elHeight = this.outerHeight(),
			scrollTo = -1;

		if ( elOffset.top < viewportX ) {
			// Element starts above viewport; put el top at top
			scrollTo = elOffset.top;
		} else if ( elOffset.top + elHeight > viewportX + viewportHeight ) {
			// Element ends below viewport
			if ( elHeight > viewportHeight ) {
				// Too tall to fit into viewport; put el top at top
				scrollTo = elOffset.top;
			} else {
				// Fits into viewport; put el bottom at bottom
				scrollTo = elOffset.top + elHeight - viewportHeight;
			}
		} // else: element is already in viewport.

		if ( scrollTo > -1 ) {
			// Scroll the viewport to display this element
			$( 'body' ).animate( { scrollTop: scrollTo }, speed );
			// And pass this delay off to the original el's queue
			return this.delay( speed );
		}

		// Do nothing
		return this;
	};
}( jQuery ) );