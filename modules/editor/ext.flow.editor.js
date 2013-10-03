( function ( $, mw ) {
	'use strict';

	mw.flow.editors = {};
	mw.flow.editor = {
		/**
		 * Specific editor to be used.
		 *
		 * @property {object}
		 */
		editor: null,

		/**
		 * Array of ve target instances.
		 *
		 * The first entry is null to make sure that the reference saved to a data-
		 * attribute is never index 0; a 0-value will make :data(flow-editor)
		 * selector not find a result.
		 *
		 * @property {object}
		 */
		editors: [null],

		init: function () {
			// determine editor instance to use, depending on availability
			var editor = 'none';
			// wikieditor is non-functional, doesn't make sense in this context
			// editor = mw.config.get( 'wgWikiEditorEnabledModules' ) ? 'wikieditor' : editor;
			editor = mw.user.options.get( 'visualeditor-enable' ) ? 'visualeditor' : editor;
			// @todo: check if VE is running

			mw.loader.using( 'ext.flow.editors.' + editor, function () {
				mw.flow.editor.editor = mw.flow.editors[editor];
			} );
		},

		/**
		 * @param {jQuery} $node
		 * @param {string} [content] Existing content to load
		 */
		load: function ( $node, content ) {
			/**
			 * When calling load(), init() may nog yet have completed loading the
			 * dependencies. To make sure it doesn't break, this will in interval,
			 * check for it and only start loading once initialization is complete.
			 */
			var load = function ( $node, content ) {
				if ( mw.flow.editor.editor === null ) {
					return;
				} else {
					clearTimeout( interval );
				}

				// quit early if editor is already loaded
				if ( mw.flow.editor.getEditor( $node ) ) {
					return;
				}

				if ( ! content ) {
					content = '';
				}

				mw.flow.editor.create( $node, content );
			},
			interval = setInterval( load.bind( this, $node, content ), 10 );
		},

		/**
		 * @param {jQuery} $node
		 */
		destroy: function ( $node ) {
			var editor = mw.flow.editor.getEditor( $node );

			if ( editor ) {
				editor.destroy();

				// destroy reference
				mw.flow.editor.editors[$.inArray( editor, mw.flow.editor.editors )] = null;
				$node
					.removeData( 'flow-editor' )
					.show();
			}
		},

		/**
		 * Get the editor's text format.
		 *
		 * @param {jQuery} $node
		 * @return {string}
		 */
		getFormat: function () {
			return mw.flow.editor.editor.format;
		},

		/**
		 * Get the raw, unconverted, content, in the current editor's format.
		 *
		 * @param {jQuery} $node
		 * @return {string}
		 */
		getRawContent: function ( $node ) {
			var editor = mw.flow.editor.getEditor( $node );
			return editor.getRawContent() || '';
		},

		/**
		 * Get the content, in wikitext format.
		 *
		 * @param {jQuery} $node
		 * @return {string}
		 */
		getContent: function ( $node ) {
			var content = mw.flow.editor.getRawContent( $node );
			return mw.flow.parsoid.convert( mw.flow.editor.getFormat(), 'wikitext', content );
		},

		/**
		 * Initialize an editor object with given content & tie it to the given node.
		 *
		 * @param {jQuery} $node
		 * @param {string} content
		 * @return {object}
		 */
		create: function ( $node, content ) {
			$node.data( 'flow-editor', mw.flow.editor.editors.length );
			mw.flow.editor.editors.push( new mw.flow.editor.editor( $node, content ) );
			return mw.flow.editor.getEditor( $node );
		},

		/**
		 * Returns editor object associated with a given node.
		 *
		 * @param {jQuery} $node
		 * @return {object}
		 */
		getEditor: function ( $node ) {
			return mw.flow.editor.editors[$node.data( 'flow-editor' )];
		}
	};
	$( mw.flow.editor.init );
} ( jQuery, mediaWiki ) );
