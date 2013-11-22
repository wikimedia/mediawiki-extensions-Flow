( function( $, mw ) {
	/**
	 * Set the selection in a textarea
	 * With thanks to Mark on StackOverflow.
	 * This part is licensed under CC-BY-SA.
	 * <http://stackoverflow.com/a/841121/1552547>
	 * @param  int start Start position in characters to select
	 * @param  int end   End position in characters to select (optional)
	 * @return jQuery    The jQuery object passed in, for chaining.
	 */
	$.fn.selectRange = function(start, end) {
		if( !end ) end = start;
		return this.each( function() {
			if ( this.setSelectionRange ) {
				this.focus();
				this.setSelectionRange( start, end );
			} else if ( this.createTextRange ) {
				var range = this.createTextRange();
				range.collapse( true );
				range.moveEnd( 'character', end );
				range.moveStart( 'character', start );
				range.select();
			}
		} );
	};
} )( jQuery, mediaWiki );
