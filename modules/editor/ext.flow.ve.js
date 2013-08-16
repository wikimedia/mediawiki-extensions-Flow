( function( $, mw ) {
mw.flow.ve = {
	/**
	 * Type of content to use (html or wikitext)
	 *
	 * @var string
	 */
	contentType: 'html',

	/**
	 * @param jQuery $node
	 * @param string content
	 */
	load: function( $node, content ) {
		// ve does not "convert" a textarea
		$node = $( '<div>' ).insertAfter( $node.hide() );
		new ve.init.sa.Target( $node, ve.createDocumentFromHtml( content ) );
	},

	/**
	 * @param jQuery $node
	 */
	unload: function( $node ) {
		// @todo
	},

	/**
	 * @param jQuery $node
	 * @return string
	 */
	getContent: function( $node ) {
		// @todo
	}
};
} )( jQuery, mediaWiki );
