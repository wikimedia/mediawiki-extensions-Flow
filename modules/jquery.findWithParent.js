( function ( $, undefined ) {
	/**
	 * Gives support to find parent elements using .closest with less-than selector syntax.
	 * @example jQueryFindWithParent( $div, "< html div < body" ); // finds a parent of $div that is html, then finds a child div of $html, then finds a parent of div that is $body, and returns $body
	 * @param {jQuery|Element|String} $context
	 * @param {String} selector
	 * @returns {jQuery}
	 */
	function jQueryFindWithParent( $context, selector ) {
		var matches;

		$context = $( $context );
		selector = $.trim( selector );

		while ( selector && ( matches = selector.match(/(.*?(?:^|[>\s+~]))(<\s*[^>\s+~]+)(.*?)$/) ) ) {
			if ( $.trim( matches[ 1 ] ) ) {
				$context = $context.find( matches[ 1 ] );
			}
			if ( $.trim( matches[ 2 ] ) ) {
				$context = $context.closest( matches[ 2 ].substr( 1 ) );
			}
			selector = $.trim( matches[ 3 ] );
		}

		if ( selector ) {
			$context = $context.find( selector );
		}

		return $context;
	}

	$.findWithParent = jQueryFindWithParent;
	$.fn.findWithParent = function ( selector ) {
		return jQueryFindWithParent( this, selector );
	};
}( jQuery ) );