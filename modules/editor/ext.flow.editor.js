( function( $, mw ) {
mw.flow.editors = {};
mw.flow.editor = {
	/**
	 * Specific editor to be used; VE if available.
	 *
	 * @var object
	 */
	editor: null,

	/**
	 * Array of ve target instances.
	 *
	 * The first entry is null to make sure that the reference saved to a data-
	 * attribute is never index 0; a 0-value will make :data(flow-editor)
	 * selector not find a result.
	 *
	 * @var object
	 */
	editors: [null],

	init: function() {
		// determine editor instance to use (depending on whether VE is enabled or not)
		var editor = mw.user.options.get( 'visualeditor-enable' ) ? 've' : 'none';
		mw.loader.using( 'ext.flow.editors.' + editor, function() {
			mw.flow.editor.editor = mw.flow.editors[editor];
		} );
	},

	/**
	 * @param jQuery $node
	 * @param string[optional] id 32-char rev_id, to load existing content
	 * @param string[optional] container, to load existing content
	 */
	load: function( $node, id, container ) {
		// if existing editor is loaded, destroy it first
		if ( mw.flow.editor.getEditor( $node ) ) {
			mw.flow.editor.destroy( $node );
		}

		if ( id && container ) {
			new mw.Api()
				.get( {
					action: 'query',
					list: 'flow-revision-content',
					revid: id, // e.g. '05021b0a3015fc3eb5dbb8f6b11bc701'
					revcontainer: container, // e.g. 'storage.post'
					revformat: mw.flow.editor.editor.contentType // e.g. 'html'
				} )
				.done( function( response ) {
					var content = response.query['flow-revision-content'].content;

					// @todo: convert to html, via parsoid (if needed)
					// @todo: or request html right away

					mw.flow.editor.createEditor( $node, content );
				} )
				.fail( function() {
					// @todo: proper error handling
					alert( 'Could not fetch content' );
				} );
		} else {
			mw.flow.editor.createEditor( $node, '' );
		}
	},

	/**
	 * @param jQuery $node
	 */
	destroy: function( $node ) {
		var editor = mw.flow.editor.getEditor( $node );
		editor.destroy();

		// destroy reference
		mw.flow.editor.editors[$.inArray( editor, mw.flow.editor.editors )] = null;
		$node
			.removeData( 'flow-editor' )
			.show();
	},

	/**
	 * Get the content, in HTML format.
	 *
	 * @param jQuery $node
	 * @return string
	 */
	getContent: function( $node ) {
		var editor = mw.flow.editor.getEditor( $node ),
			content = editor.getContent();

		if ( mw.flow.editor.editor.contentType !== 'html' ) {
			// @todo: convert to html, via parsoid (or let api deal with it?)
		}

		return content;
	},

	/**
	 * Initialize an editor object with given content & tie it to the given node.
	 *
	 * @param jQuery $node
	 * @param string content
	 * @return object
	 */
	createEditor: function( $node, content ) {
		$node.data( 'flow-editor', mw.flow.editor.editors.length );
		mw.flow.editor.editors.push( new mw.flow.editor.editor( $node, content ) );
		return mw.flow.editor.getEditor( $node );
	},

	/**
	 * Returns editor object associated with a given node.
	 *
	 * @param jQuery $node
	 * @return object
	 */
	getEditor: function( $node ) {
		return mw.flow.editor.editors[$node.data( 'flow-editor' )];
	}
};
$( mw.flow.editor.init );
} )( jQuery, mediaWiki );
