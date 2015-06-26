( function ( $ ) {
	/**
	 * Flow editor switcher widget
	 *
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
			classes: [ 'flow-ui-editorSwitcherWidget-triggerInput' ]
		} );

		this.triggerInput.$input
			.on( 'focusin', this.onTriggerInputFocusIn.bind( this ) )
			.on( 'focusout', this.onTriggerInputFocusOut.bind( this ) );

		this.initializeEditors( config.editor, config.content );

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
		this.setEditor( initialEditor || 'wikitext', content );
	};

	/**
	 * Sets the editor as the present one in the widget
	 *
	 * @param {string} editor Key of the requested editor
	 * @return {jQuery.Promise} Promise that resolves when the new editor is ready
	 *  or is rejected if there was a problem setting the new editor.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setEditor = function ( editorKey, content ) {
		var editor = this.getEditor( editorKey ),
			deferred = $.Deferred();

		if ( !editor ) {
			// TODO: Display errors properly
			return $.Deferred().reject().promise();
		}

		content = content !== undefined ? content : this.content;

		// Set the new editor as the current one
		this.setActiveEditor( editor );

		// Get data into the new editor
		editor.widget.reloadContent( content )
			.then(
				function () {

					// Resolve the promise
					deferred.resolve();
				},
				// Failure
				function () {
					// TODO: Show error
					deferred.reject();
				}
			);

		return deferred;
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
				// Hide the current editor and bring forth the wikitext editor
				activeEditor.widget.toggle( false );

				// Show the initial input
				this.triggerInput.toggle( true );
			} else {
				// Hide the initial input
				this.triggerInput.toggle( false );

				// Show the current editor
				activeEditor.widget.toggle( true );

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
	 * @return {mw.flow.ui.AbstractEditor} Currently active editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getActiveEditor = function () {
		return this.activeEditor;
	};

	/**
	 * Set the currently active editor
	 *
	 * @param {Object} editor Currently active editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setActiveEditor = function ( editor ) {
		var currentEditor;

		// Hide and detach the current editor if it exists
		currentEditor = this.getActiveEditor();
		if ( currentEditor && currentEditor.widget ) {
			currentEditor.widget.toggle( false );
			currentEditor.widget.$element.detach();
		}

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
				focusout: 'onEditorFocusOut'
			} );
		}
		// Append the editor
		this.$element.append( editor.widget.$element );

		if ( !this.isCollapsed() ) {
			// Display the editor
			editor.widget.toggle( true );
		}

		// Update the active editor
		this.activeEditor = editor;
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

	mw.flow.ui.EditorSwitcherWidget.prototype.destroy = function () {
		var editor;

		for ( editor in this.editors ) {
			this.editors[editor].widget.disconnect( this );
			this.editors[editor].widget.destroy();
		}
	};

}( jQuery ) );
