( function ( $, mw ) {
	'use strict';

	mw.flow = mw.flow || {}; // create mw.flow globally
	mw.flow.editors = {};

	// This is more of an EditorFacade/EditorDispatcher or something, and should be renamed.
	// It's not the base class, nor is it an actual editor.
	mw.flow.editor = {
		/**
		 * Specific editor to be used.
		 *
		 * @property {object}
		 */
		editor: null,

		/**
		 * Array of target instances.
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
			mw.flow.editor.loadEditor();
		},

		loadEditor: function ( editorIndex ) {
			var editorList = mw.config.get( 'wgFlowEditorList' ),
				editor;

			if ( !editorIndex ) {
				editorIndex = 0;
			}

			if ( editorList[editorIndex] ) {
				editor = editorList[editorIndex];
			} else {
				editor = 'none';
			}

			mw.loader.using( 'ext.flow.editors.' + editor, function () {
				// Some editors only work under certain circumstances
				if (
					!mw.flow.editors[editor].static.isSupported()
				) {
					mw.flow.editor.loadEditor( editorIndex + 1 );
				} else {
					mw.flow.editor.editor = mw.flow.editors[editor];
				}
			} );
		},

		/**
		 * @param {jQuery} $node
		 * @param {string} [content] Existing content to load, in any format
		 * @param {string} [contentFormat] The format that content is in, or null (defaults to wikitext)
		 * @return {jQuery.Deferred} Will resolve once editor instance is loaded
		 */
		load: function ( $node, content, contentFormat ) {
			/**
			 * When calling load(), init() may not yet have completed loading the
			 * dependencies. To make sure it doesn't break, this will in interval,
			 * check for it and only start loading once initialization is complete.
			 */
			var load = function ( $node, content, contentFormat ) {
				if ( mw.flow.editor.editor === null ) {
					return;
				} else {
					clearTimeout( interval );
				}

				if ( contentFormat === undefined ) {
					contentFormat = 'wikitext';
				}

				// quit early if editor is already loaded
				if ( mw.flow.editor.getEditor( $node ) ) {
					deferred.resolve();
					return;
				}

				if ( content ) {
					try {
						content = mw.flow.parsoid.convert( contentFormat, mw.flow.editor.getFormat(), content );
					} catch ( e ) {
						$( '<div>' ).flow( 'showError', e.getErrorInfo() ).insertAfter( $node );
						return;
					}
				} else {
					content = '';
				}

				mw.flow.editor.create( $node, content );
				deferred.resolve();
			},
			deferred = $.Deferred(),
			interval = setInterval( $.proxy( load, this, $node, content, contentFormat ), 10 );

			return deferred.promise();
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
			return mw.flow.editor.editor.static.format;
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
			var content = mw.flow.editor.getRawContent( $node ), convertedContent = '';
			try {
				convertedContent = mw.flow.parsoid.convert( mw.flow.editor.getFormat(), 'wikitext', content );
			} catch ( e ) {
				$( '<div>' ).flow( 'showError', e.getErrorInfo() ).insertAfter( $node );
			}
			return convertedContent;
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
		},

		/**
		 * Returns true if the given $node has an associated editor instance.
		 *
		 * @param {jQuery} $node
		 * @return {bool}
		 */
		exists: function ( $node ) {
			return mw.flow.editor.editors.hasOwnProperty( $node.data( 'flow-editor' ) );
		},

		focus: function( $node ) {
			var editor = mw.flow.editor.getEditor( $node );

			if ( editor && editor.focus ) {
				editor.focus();
			} else {
				$node.focus();
			}
		},

		moveCursorToEnd : function( $node ) {
			var editor = mw.flow.editor.getEditor( $node );

			if ( editor && editor.moveCursorToEnd ) {
				return editor.moveCursorToEnd();
			} else {
				$node.selectRange( $node.val().length );
			}
		}
	};
	$( mw.flow.editor.init );
} ( jQuery, mediaWiki ) );
