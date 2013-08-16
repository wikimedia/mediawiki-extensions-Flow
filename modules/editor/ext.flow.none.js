( function( $, mw ) {
mw.flow.none = {
	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var string
	 */
	contentType: 'wikitext',

	/**
	 * @param jQuery $node
	 * @param string content
	 */
	load: function( $node, content ) {
		$node.val( content );
	},

	/**
	 * @param jQuery $node
	 */
	unload: function( $node ) {

	},

	/**
	 * @param jQuery $node
	 * @return string
	 */
	getContent: function( $node ) {
		return $node.val();
	}
};
} )( jQuery, mediaWiki );
