( function ( $, mw ) {
	'use strict';

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

		/**
		/**
		 * List of editor names that are valid for use
		 *
		 * @property {Array}
		 */
		available: [],

		init: function () {
			var editorList = mw.config.get( 'wgFlowEditorList' ),
				modules = $.map( editorList, function ( value, key ) {
					return 'ext.flow.editors.' + value;
				} );

			if ( mw.flow.editor.available.length > 0 ) {
				// already initialied
				return;
			}

			mw.loader.using( modules, function() {
				var i,
					desiredEditor = mw.user.options.get( 'flow-editor' );

				mw.flow.editor.available = [];

				for ( i = 0; i < editorList.length; ++i ) {
					if ( !mw.flow.editors[editorList[i]] ) {
						// editor failed to load
						mw.flow.debug( "[mw.flow.editor] failed to load " + editorList[i] );
						continue;
					}
					if ( mw.flow.editors[editorList[i]].static.isSupported() ) {
						mw.flow.editor.available.push( editorList[i] );
					}
				}

				if ( mw.flow.editor.available.indexOf( desiredEditor ) !== -1 ) {
					// users prefered editor is available
					mw.flow.editor.editor = mw.flow.editors[desiredEditor];
				} else if ( mw.flow.editor.available.length === 0 ) {
					// no valid editors were found
					mw.flow.debug( "[mw.flow.editor] No valid editors found" );
					return;
				} else {
					// fallback to first available editor
					mw.flow.editor.editor = mw.flow.editors[mw.flow.editor.available[0]];
				}

				// Some editors, like VE, have a bit of extra code to load.  This
				// optimistically loads other dependent modules
				if ( mw.flow.editor.editor.static.getModuleDependencies().length ) {
					setTimeout(
						function () {
							mw.loader.using( mw.flow.editor.editor.static.getModuleDependencies() );
						},
						20000
					);
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
						content = mw.flow.parsoid.convert(
							contentFormat,
							mw.flow.editor.getFormat(),
							content
						);
					} catch ( e ) {
						$( '<div>' ).flow( 'showError', e.getErrorInfo() ).insertAfter( $node );
						return;
					}
				} else {
					content = '';
				}

				mw.flow.editor.create( $node, content );

				$node.closest( 'form' ).toggleClass(
					'flow-editor-supports-preview',
					mw.flow.editor.editor.static.usesPreview()
				);

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
					.show()
					.closest( '.flow-editor' ).removeClass( 'flow-editor-' + editor.constructor.static.name );
			}
		},

		/**
		 * Get the editor's text format.
		 *
		 * @param {jQuery} $node
		 * @return {string}
		 */
		getFormat: function ( $node ) {
			var editor;

			if ( $node ) {
				editor = mw.flow.editor.getEditor( $node );
			}

			if ( editor ) {
				return editor.constructor.static.format;
			} else {
				return mw.flow.editor.editor.static.format;
			}
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
		 * @deprecated API now also accepts html content, as long as the format
		 * parameter is set. It's encouraged to use getRawContent + getFormat.
		 *
		 * @param {jQuery} $node
		 * @return {string}
		 */
		getContent: function ( $node ) {
			var content = mw.flow.editor.getRawContent( $node ), convertedContent = '';
			try {
				convertedContent = mw.flow.parsoid.convert(
					mw.flow.editor.getFormat( $node ),
					'wikitext',
					content
				);
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
			$node.data( 'flow-editor', mw.flow.editor.editors.length )
				.closest( '.flow-editor' ).addClass( 'flow-editor-' + mw.flow.editor.editor.static.name );

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

		/**
		 * Changes the default editor to desiredEditor and converts $node to that
		 * type of editor.
		 *
		 * @todo Should support $node containing multiple items
		 *
		 * @param {jQuery} $node
		 * @return {jQuery.Deferred} Will resolve once editor instance is loaded
		 */
		switchEditor: function ( $node, desiredEditor ) {
			var content, format, deferred,
				editor = mw.flow.editor.getEditor( $node );

			// either $node is not an editor, or the desiredEditor is
			// not available.
			if ( !editor || mw.flow.editor.available.indexOf( desiredEditor ) === -1 ) {
				return $.Deferred().reject().promise();
			}

			content = editor.getRawContent();
			format = editor.constructor.static.format;

			mw.flow.editor.editor = mw.flow.editors[desiredEditor];

			mw.flow.editor.destroy( $node );
			deferred = mw.flow.editor.load( $node, content, format );

			// update the user preferences
			new mw.Api().saveOption( 'flow-editor', desiredEditor );
			// ensure we also see that preference in the current page
			mw.user.options.set( 'flow-editor', desiredEditor );

			return deferred;
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
	$.ready( mw.flow.editor.init );
} ( jQuery, mediaWiki ) );
