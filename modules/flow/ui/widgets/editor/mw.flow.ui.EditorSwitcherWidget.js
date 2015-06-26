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

		this.editors = {};
		this.initializeEditors();

		// Initialize
		this.$element
			.addClass( 'flow-ui-editorControlsWidget' );
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
		visualeditor: mw.flow.ui.visualEditorWidget
	};

	/* Methods */

	/**
	 * Initialize available editors
	 *
	 * @param {string} [initialEditor] The key for the initial editor to use
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.initializeEditors = function () {
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
				availableEditors.indexOf( editor ) &&
				this.constructor.static.editorDefinitions[ editor ].static.isSupported()
			) {
				this.editors[ editor ] = {
					editorClass: this.constructor.static.editorDefinitions[ editor ],
					widget: null
				};
			}
		}
	};

	/**
	 * Sets the editor as the present one in the widget
	 *
	 * @param {string} editor Key of the requested editor
	 * @return {jQuery.Promise} Promise that resolves when the new editor is ready
	 *  or is rejected if there was a problem setting the new editor.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setEditor = function ( editorKey ) {
		var content, currentEditor,
			widget = this,
			editor = this.getEditor( editorKey ),
			deferred = $.Deferred();

		if ( !editor ) {
			// TODO: Display errors?
			return $.Deferred().promise().reject();
		}

		// Check if editor is already instantiated and loaded
		if ( !editor.widget ) {
			// Instantiate the editor
			editor.widget = new editor.editorClass( {
				showSwitcher: this.isSwitchable()
			} );
		}

		// Get data into the new editor
		editor.widget.reloadContent( content )
			.then(
				function () {
					// Hide the current editor if it exists
					currentEditor = widget.getActiveEditor();
					if ( currentEditor && currentEditor.widget ) {
						currentEditor.widget.toggle( false );
					}

					// Set the new editor as the current one
					widget.setActiveEditor( editor );

					// Show the new editor
					editor.toggle( true );

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
	 * @param {mw.flow.ui.AbstractEditor} editor Currently active editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setActiveEditor = function ( editor ) {
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
}( jQuery ) );
