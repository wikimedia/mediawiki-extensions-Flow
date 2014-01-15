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

		var viewport_x = $( window ).scrollTop(),
			viewport_height = $( window ).height(),
			el_offset = this.offset(),
			el_height = this.outerHeight(),
			scroll_to = -1;

		if ( el_offset.top < viewport_x ) {
			// Element starts above viewport; put el top at top
			scroll_to = el_offset.top;
		} else if ( el_offset.top + el_height > viewport_x + viewport_height ) {
			// Element ends below viewport
			if ( el_height > viewport_height ) {
				// Too tall to fit into viewport; put el top at top
				scroll_to = el_offset.top;
			} else {
				// Fits into viewport; put el bottom at bottom
				scroll_to = el_offset.top + el_height - viewport_height;
			}
		} // else: element is already in viewport.

		if ( scroll_to > -1 ) {
			// Scroll the viewport to display this element
			$( 'body' ).animate( { scrollTop: scroll_to }, speed );
			// And pass this delay off to the original el's queue
			return this.delay( speed );
		}

		// Do nothing
		return this;
	};
}( jQuery ) );