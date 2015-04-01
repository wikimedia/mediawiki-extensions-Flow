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

		init: function () {
			var editorList = mw.config.get( 'wgFlowEditorList' ),
				index = editorList.indexOf( mw.user.options.get( 'flow-editor' ) );

			// determine editor instance to use, depending on availability
			mw.flow.editor.loadEditor( index );
		},

		loadEditor: function ( editorIndex ) {
			var editorList = mw.config.get( 'wgFlowEditorList' ),
				editor;

			if ( !editorIndex || editorIndex < 0 || editorIndex >= editorList.length ) {
				editorIndex = 0;
			}

			if ( editorList[editorIndex] ) {
				editor = editorList[editorIndex];
			} else {
				editor = 'none';
			}

			mw.loader.using( 'ext.flow.editors.' + editor, function () {
				// Some editors only work under certain circumstances
				if ( !mw.flow.editors[editor].static.isSupported() ) {
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
		 * @return {jQuery.Promise} Will resolve once editor instance is loaded
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

				mw.flow.parsoid.convert( contentFormat, mw.flow.editor.getFormat( $node ), content )
					.done( function( content ) {
						mw.flow.editor.create( $node, content );
						deferred.resolve();
					})
					.fail( function() {
						deferred.reject();
					});
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
		 * @todo Should support $node containing multiple editing nodes, such
		 *  as selecting all active editors in the page and switching all of
		 *  them to the desiredEditor. Currently you will need to $node.each()
		 *  and call switchEditor for each iteration.
		 *
		 * @param {jQuery} $node
		 * @param {string} desiredEditor
		 * @return {jQuery.Promise} Will resolve once editor instance is loaded
		 */
		switchEditor: function ( $node, desiredEditor ) {
			var content, format,
				editorList = mw.config.get( 'wgFlowEditorList' ),
				editor = mw.flow.editor.getEditor( $node ),
				deferred = $.Deferred(),
				performSwitch = function () {
					if ( mw.flow.editors[desiredEditor].static.isSupported() ) {
						content = editor.getRawContent();
						format = editor.constructor.static.format;

						mw.flow.editor.editor = mw.flow.editors[desiredEditor];

						mw.flow.editor.destroy( $node );
						mw.flow.editor.load( $node, content, format );

						deferred.resolve();
					} else {
						deferred.reject( 'editor-not-supported' );
					}
				};

			if ( !editor ) {
				// $node is not an editor
				deferred.reject( 'not-an-editor' );
			} else if ( editorList.indexOf( desiredEditor ) === -1 ) {
				// desiredEditor does not exist
				deferred.reject( 'unknown-editor-type' );
			} else {
				mw.loader.using( 'ext.flow.editors.' + desiredEditor ).then(
					performSwitch,
					function() {
						deferred.reject( 'fail-loading-editor' );
					}
				);
			}

			deferred.then(
				function() {
					if ( !mw.user.isAnon() ) {
						// update the user preferences; no preferences for anons
						new mw.Api().saveOption( 'flow-editor', desiredEditor );
						// ensure we also see that preference in the current page
						mw.user.options.set( 'flow-editor', desiredEditor );
					}
				},
				function( rejectionCode ) {
					mw.flow.debug( '[switchEditor] Could not switch to ' + desiredEditor + ' : ' + rejectionCode );
				}
			);

			return deferred.promise();
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
