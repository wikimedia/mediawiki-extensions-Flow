( function ( $ ) {
	/**
	 * Flow editor switcher widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [placeholder] Placeholder text for the edit area
	 * @cfg {string} [content] Initial content
	 * @cfg {string} [contentFormat='wikitext'] Format of initial contents
	 */
	mw.flow.ui.EditorSwitcherWidget = function mwFlowUiEditorSwitcherWidget( config ) {
		var widget = this;
		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorSwitcherWidget.parent.call( this, config );

		this.placeholder = config.placeholder || '';
		this.initialContent = config.content || '';
		this.contentFormat = config.contentFormat || 'wikitext';
		this.activeEditor = null; //TODO rename to this.activeEditorName
		this.editors = {};
		this.availableEditors = mw.config.get( 'wgFlowEditorList' )
			.map( function ( editor ) {
				// Map 'none' to 'wikitext' for backwards compatibility
				return editor === 'none' ? 'wikitext' : editor;
			} )
			.filter( function ( editor ) {
				return widget.constructor.static.editorDefinitions[editor].static.isSupported();
			} );

		this.triggerInput = new OO.ui.TextInputWidget( {
			multiline: false,
			placeholder: this.placeholder,
			classes: [ 'flow-ui-editorSwitcherWidget-trigger-input' ]
		} );

		this.triggerInput.$input
			.on( 'focusin', this.onTriggerInputFocusIn.bind( this ) )
			.on( 'focusout', this.onTriggerInputFocusOut.bind( this ) );

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

	/**
	 * Get the editor that is currently active.
	 * @return {mw.flow.ui.AbstractEditor|null} Active editor, if any
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getActiveEditor = function () {
		return this.activeEditor && this.editors[this.activeEditor] || null;
	};

	/**
	 * Get the next editor after the currently active one.
	 * @return {string} Name of the next editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getNextEditorName = function () {
		return this.availableEditors[
			( this.availableEditors.indexOf( this.activeEditor ) + 1 ) %
			this.availableEditors.length
		];
	};

	/**
	 * Create an editor and add it to this.editors.
	 *
	 * This method is safe to call multiple times. If the requested editor has already been
	 * created, the existing instance is returned.
	 *
	 * @param {string} name Editor name
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getEditor = function ( name ) {
		if ( !this.editors[name] ) {
			this.editors[name] = new this.constructor.static.editorDefinitions[name]( {
				switchable: this.isSwitchable(),
				placeholder: this.placeholder
			} );
			this.editors[name].connect( this, {
				focusin: 'onEditorFocusIn',
				focusout: 'onEditorFocusOut',
				'switch': 'onEditorSwitch'
			} );
		}
		return this.editors[name];
	};

	/**
	 * Switch to a different editor.
	 *
	 * This initializes the new editor (if needed), hides and detaches the old editor,
	 * attaches and shows the new editor, and loads the content from the old editor
	 * into the new editor.
	 *
	 * @param {string} name Name of editor to switch to.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.switchEditor = function ( name ) {
		var oldEditor = this.getActiveEditor(),
			oldContent = oldEditor && oldEditor.getContent() || this.initialContent,
			oldFormat = this.contentFormat,
			newEditor = this.getEditor( name ),
			newFormat = newEditor.getFormat(),
			loadPromise = newEditor.load(),
			widget = this;

		// TODO figure out some sort of halfway mid-switch state
		this.activeEditor = name;
		this.contentFormat = newFormat;

		return $.when(
			this.convertContent( oldContent, oldFormat, newFormat ),
			loadPromise
		)
			.then( function ( newContent ) {
				newEditor.setup( newContent );

				// Hide and detach the current editor if it exists
				if ( oldEditor ) {
					oldEditor.toggle( false );
					oldEditor.$element.detach();
				}

				// Append the new editor
				widget.$element.append( newEditor.$element );

				if ( !widget.isCollapsed() ) {
					// Display the new editor
					newEditor.toggle( true );
					newEditor.focus();
				}
			} );
	};

	/**
	 * Convert content from one format to another.
	 *
	 * It's safe to call this function with fromFormat=toFormat: in that case, the returned promise
	 * will be resolved immediately with the original content.
	 *
	 * @param {string} content Content in old format
	 * @param {string} fromFormat Old format name
	 * @param {string} toFormat New format name
	 * @return {jQuery.Promise} Promise resolved with content converted to new format
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.convertContent = function ( content, fromFormat, toFormat ) {
		//TODO handle errors
		if ( fromFormat === toFormat ) {
			return $.Deferred().resolve( content ).promise();
		}

		return new mw.Api().post( {
			action: 'flow-parsoid-utils',
			from: fromFormat,
			to: toFormat,
			content: content,
			title: mw.config.get( 'wgPageName' )
		} )
			.then( function ( data ) {
				return data['flow-parsoid-utils'].content;
			}, function () {
				// Map Parsoid failure to 'failed-parsoid-convert'
				return 'failed-parsoid-convert';
			} );
	};

	/**
	 * Respond to editor switch event. Switch either to the requested editor or
	 * to the next editor available.
	 *
	 * @param {string} [name] The key of the requested editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorSwitch = function ( name ) {
		if ( !this.isSwitchable() ) {
			return;
		}
		if ( !name || this.availableEditors.indexOf( name ) === -1 ) {
			name = this.getNextEditorName();
		}
		this.switchEditor( name );
	};

	// TODO reduce focus handlers, use bubbling
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusIn = function () {
		this.$element.addClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusOut = function () {
		this.$element.removeClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onTriggerInputFocusIn = function () {
		this.toggleCollapsed( false );
		this.$element.addClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onTriggerInputFocusOut = function () {
		this.$element.removeClass( 'flow-ui-editorSwitcherWidget-focused' );
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
				if ( this.editors[editor] ) {
					this.editors[editor].togglePending( this.pending );
				}
			}

		}
	};

	/**
	 * Focus on the editor.
	 * This only works on non collapsed mode when the editor is visible.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.focus = function () {
		if ( !this.isCollapsed() && this.getActiveEditor() ) {
			this.getActiveEditor().focus();
		}
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
				if ( activeEditor ) {
					activeEditor.toggle( false );
				}

				// Show the initial input
				this.triggerInput.toggle( true );
			} else {
				// Hide the initial input
				this.triggerInput.toggle( false );

				if ( activeEditor ) {
					activeEditor.toggle( true );
				} else {
					this.switchEditor( this.getNextEditorName() );
				}
			}

			this.emit( 'collapsed', this.collapsed );
		}
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.isCollapsed = function () {
		return this.collapsed;
	};

	/**
	 * Check if the widget is switchable. If there is only one editor available
	 * then there should not be switching possible.
	 *
	 * @return {boolean} Editors are switchable
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.isSwitchable = function () {
		return this.availableEditors.length > 1;
	};

	/**
	 * Destroy the widget
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.destroy = function () {
		var editor;

		for ( editor in this.editors ) {
			this.editors[editor].disconnect( this );
			this.editors[editor].destroy();
		}
	};

}( jQuery ) );
