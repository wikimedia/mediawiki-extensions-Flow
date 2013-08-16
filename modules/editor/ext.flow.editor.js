( function( $, mw ) {
mw.flow.editor = {
	editor: mw.flow.none, // @todo: only VE if enabled, other editor otherwise

	init: function() {

	},

	/**
	 * @param jQuery $node
	 * @param string[optional] id 32-char rev_id, to load existing content
	 * @param string[optional] container, to load existing content
	 */
	load: function( $node, id, container ) {
		// @todo: check if editor not already loaded

		if ( id && container ) {
			new mw.Api()
				.get( {
					action: 'query',
					list: 'flow-revision-content',
					revid: id, // '05021b0a3015fc3eb5dbb8f6b11bc701'
					revcontainer: container // 'storage.post'
				} )
				.done( function( response ) {
					mw.flow.editor.editor.load(
						$node,
						response.query['flow-revision-content'][mw.flow.editor.editor.contentType]
					);
				} )
				.fail( function() {
					// @todo: proper error handling
					alert( 'Could not fetch content' );
				} );
		} else {
			mw.flow.editor.editor.load(
				$node,
				''
			);
		}
	},

	/**
	 * @param jQuery $node
	 */
	unload: function( $node ) {
		mw.flow.editor.editor.unload( $node );
	},

	/**
	 * @param jQuery $node
	 * @return string
	 */
	getContent: function( $node ) {
		return mw.flow.editor.editor.getContent( $node );
	}
};

$( mw.flow.editor.init );
} )( jQuery, mediaWiki );
