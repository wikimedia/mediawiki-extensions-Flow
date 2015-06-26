( function ( $ ) {
	/**
	 * Flow editor switcher widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.EditorSwitcherWidget = function mwFlowUiEditorSwitcherWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorSwitcherWidget.parent.call( this, config );

		this.placeholder = config.placeholder || '';
		this.content = config.content || '';

		this.triggerInput = new OO.ui.TextInputWidget( {
			multiline: false,
			placeholder: config.placeholder,
			classes: [ 'flow-ui-editorSwitcherWidget-trigger-input' ]
		} );

		this.triggerInput.$input
			.on( 'focusin', this.onTriggerInputFocusIn.bind( this ) )
			.on( 'focusout', this.onTriggerInputFocusOut.bind( this ) );

		this.initializeEditors( config.editor, config.content );
		this.toggleCollapsed( !config.expanded );

		this.togglePending( false );
		// Initialize
		this.$element
			.append( this.triggerInput.$element )
			.addClass( 'flow-ui-editorSwitcherWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorSwitcherWidget, OO.ui.Widget );

	/* Static Methods */

	/**
	 * Define the available editors and their widget constructors
	 *
	 * @property {Object}
	 */
	mw.flow.ui.EditorSwitcherWidget.static.editorDefinitions = {
		wikitext: mw.flow.ui.WikitextEditorWidget,
		visualeditor: mw.flow.ui.VisualEditorWidget
	};

	/* Methods */
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusIn = function () {
		this.$element.addClass( 'flow-ui-editorSwitcherWidget-focused' );
		console.log( 'editor focusin' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusOut = function () {
		console.log( 'editor focusout' );
		this.$element.removeClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onTriggerInputFocusIn = function () {
		console.log( 'trigger focusin' );
		this.toggleCollapsed( false );
		this.$element.addClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onTriggerInputFocusOut = function () {
		console.log( 'trigger focusout' );
		this.$element.removeClass( 'flow-ui-editorSwitcherWidget-focused' );
	};

	/**
	 * Respond to editor switch event. Switch either to the requested editor or
	 * to the next editor available.
	 *
	 * @param {string} [editorKey] The key of the requested editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorSwitch = function ( editorKey ) {
		var editor = editorKey ? this.getEditor( editorKey ) : this.getNextEditor();

		this.setEditor( editor );
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.getEditorFormat = function () {
		var editor = this.getActiveEditor();
		return editor && editor.widget && editor.widget.getFormat();
	};

	/**
	 * Toggle the pending state of the editor
	 *
	 * @param {boolean} [pending] Widget is pending
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.togglePending = function ( pending ) {
		var editor;

		pending = pending !== undefined ? pending : !this.pending;

		if ( this.pending !== pending ) {
			this.pending = pending;

			// Set all editors to the same state
			for ( editor in this.editors ) {
				if ( this.editors[editor].widget ) {
					this.editors[editor].widget.togglePending( this.pending );
				}
			}

		}
	};

	/**
	 * Get the next editor available
	 *
	 * @param {string} [editorKey] The key of the requested editor
	 * @return {Object} Editor definition
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getNextEditor = function ( editorKey ) {
		var keys, startIndex, editor, existingEditor;

		if ( !this.isSwitchable() ) {
			return null;
		}

		// Return editor if it is given and exists
		editor = this.getEditor( editorKey );
		if ( editor ) {
			return editor;
		}

		editor = this.getActiveEditor();
		existingEditor = ( editor && editor.widget && editor.widget.getName() ) || '';

		// Otherwise look for the next editor
		keys = Object.keys( this.editors );
		startIndex = keys.indexOf( existingEditor );
		if ( startIndex === -1 || startIndex >= keys.length ) {
			// Return the editor of the first key
			return this.getEditor( keys[0] );
		} else {
			// Return the editor in the next key
			return this.getEditor( keys[startIndex + 1] );
		}
	};

	/**
	 * Initialize available editors
	 *
	 * @param {string} [initialEditor] The key for the initial editor to use
	 * @param {string} [content] Initial content to load to the editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.initializeEditors = function ( initialEditor, content ) {
		var editor,
			availableEditors = mw.config.get( 'wgFlowEditorList' );

		// Transform 'none' to 'wikitext'
		if ( availableEditors.indexOf( 'none' ) > -1 ) {
			availableEditors.splice( availableEditors.indexOf( 'none' ), 1 );
			availableEditors.push( 'wikitext' );
		}

		// Store all available editors
		this.editors = {};
		for ( editor in this.constructor.static.editorDefinitions ) {
			if (
				availableEditors.indexOf( editor ) > -1 &&
				this.constructor.static.editorDefinitions[ editor ] &&
				this.constructor.static.editorDefinitions[ editor ].static.isSupported()
			) {
				this.editors[ editor ] = {
					editorClass: this.constructor.static.editorDefinitions[ editor ],
					initParams: {
						placeholder: this.placeholder
					},
					widget: null
				};
			}
		}

		// Make sure the wikitext editor exists. If it doesn't, add it
		if ( !this.editors.wikitext ) {
			this.editors.wikitext = {
				editorClass: mw.flow.ui.WikitextEditorWidget,
				initParams: {
					placeholder: this.placeholder
				},
				widget: null
			};
		}

		// Initialize the editor
		this.setEditorByKey( initialEditor || 'wikitext', content );
	};

	/**
	 * Sets the editor as the present one by its key.
	 *
	 * @param {string} [editorKey] The key of the requested editor
	 * @param {string} [content] Content to display in the editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setEditorByKey = function ( editorKey, content ) {
		this.setEditor( this.getEditor( editorKey ), content );
	};

	/**
	 * Sets the editor as the present one in the widget
	 *
	 * @param {Object} editor Requested editor
	 * @param {string} [content] Content to display in the editor
	 * @return {jQuery.Promise} Promise that resolves when the new editor is ready
	 *  or is rejected if there was a problem setting the new editor.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setEditor = function ( editor, content ) {
		var widget = this;

		if ( !editor ) {
			// TODO: Display errors properly
			return $.Deferred().reject().promise();
		}

		content = content !== undefined ? content : this.content;

		// Set the new editor as the current one
		this.emit( 'editorSwitch', true );
		return this.setActiveEditor( editor )
			.then( editor.widget.reloadContent.bind( editor, content ) )
			// Get data into the new editor
			.then( function ( newContent ) {
				widget.content = newContent;
				widget.focus();
				widget.emit( 'editorSwitch', false );
			} );
	};

	/**
	 * Focus on the editor.
	 * This only works on non collapsed mode when the editor is visible.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.focus = function () {
		if ( !this.isCollapsed() ) {
			this.getActiveEditor().widget.focus();
		}
	};

	/**
	 * Get the specified editor widget.
	 *
	 * @param {string} editor Requested editor
	 * @return {mw.flow.editors.AbstractEditor} Requested editor class
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getEditor = function ( editor ) {
		// The 'none' editor is deprecated, so change it to 'wikitext'
		editor = editor === 'none' ? editor = 'wikitext' : editor;

		return this.editorExists( editor ) && this.editors[editor];
	};

	/**
	 * Toggle the collapsed state of the switcher.
	 * When the widget is collapsed, display the initial input. Otherwise, display
	 * the current editor.
	 *
	 * @param {boolean} collapsed Editor is collpased
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.toggleCollapsed = function ( collapsed ) {
		var activeEditor = this.getActiveEditor();

		collapsed = collapsed !== undefined ? collapsed : !this.collapsed;

		if ( this.collapsed !== collapsed ) {
			this.collapsed = collapsed;

			if ( this.collapsed ) {
				// Hide the current editor and bring forth the initial input
				activeEditor.widget.toggle( false );

				// Show the initial input
				this.triggerInput.toggle( true );
			} else {
				// Hide the initial input
				this.triggerInput.toggle( false );

				// Reload the editor
				this.setEditor( activeEditor, this.content );
			}

			this.emit( 'collapsed', this.collapsed );
		}
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.isCollapsed = function () {
		return this.collapsed;
	};
	/**
	 * Check if an editor exists
	 *
	 * @param {string} editor Requested editor
	 * @return {boolean} Editor exists
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.editorExists = function ( editor ) {
		// The 'none' editor is deprecated, so change it to 'wikitext'
		editor = editor === 'none' ? editor = 'wikitext' : editor;

		return this.editors.hasOwnProperty( editor );
	};

	/**
	 * Check if an editor is already loaded
	 *
	 * @param {string} editor Requested editor
	 * @return {boolean} Editor is loaded
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.isEditorLoaded = function ( editor ) {
		// The 'none' editor is deprecated, so change it to 'wikitext'
		editor = editor === 'none' ? editor = 'wikitext' : editor;

		return this.editorExists( editor ) && this.editors[editor].widget;
	};

	/**
	 * Get the currently active editor
	 *
	 * @return {Object} Currently active editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getActiveEditor = function () {
		return this.activeEditor;
	};

	/**
	 * Set the currently active editor
	 *
	 * @param {Object} editor Currently active editor
	 * @return {jQuery.Promise} Promise that is resolved when the editor finished initializing.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setActiveEditor = function ( editor ) {
		var currentEditor, initPromise,
			widget = this;

		// Check if the editor is already instantiated and loaded
		if ( !editor.widget ) {
			// Instantiate the editor
			editor.widget = new editor.editorClass( $.extend(
				editor.initParams,
				{
					showSwitcher: this.isSwitchable()
				}
			) );
			editor.widget.connect( this, {
				focusin: 'onEditorFocusIn',
				focusout: 'onEditorFocusOut',
				'switch': 'onEditorSwitch'
			} );
			initPromise = editor.widget.initialize();
		} else {
			// No need to initialize
			initPromise = $.Deferred().resolve().promise();
		}

		currentEditor = this.getActiveEditor();

		return initPromise
			.then( function () {
				// Hide and detach the current editor if it exists
				if ( currentEditor && currentEditor.widget ) {
					currentEditor.widget.toggle( false );
					currentEditor.widget.$element.detach();
				}

				// Append the editor
				widget.$element.append( editor.widget.$element );

				if ( !widget.isCollapsed() ) {
					// Display the editor
					editor.widget.toggle( true );
					editor.widget.focus();
				}

				// Update the active editor
				widget.activeEditor = editor;
			} );
	};

	/**
	 * Check if the widget is switchable. If there is only one editor available
	 * then there should not be switching possible.
	 *
	 * @return {boolean} Editors are switchable
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.isSwitchable = function () {
		return Object.keys( this.editors ).length > 1;
	};

	/**
	 * Destroy the widget
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.destroy = function () {
		var editor;

		for ( editor in this.editors ) {
			this.editors[editor].widget.disconnect( this );
			this.editors[editor].widget.destroy();
		}
	};

}( jQuery ) );
