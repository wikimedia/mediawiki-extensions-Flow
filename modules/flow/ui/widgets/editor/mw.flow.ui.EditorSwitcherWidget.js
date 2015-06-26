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
	 * @cfg {string} [initialEditor] First editor to load. If not set, the first editor
	 *   will be the first one in wgFlowEditorList that matches the format of the provided
	 *   content (if any)
	 * @cfg {boolean} [expanded=false] Set the widget's initial loading state to be
	 *  expanded. Otherwise, the widget will initialize in collapsed state
	 */
	mw.flow.ui.EditorSwitcherWidget = function mwFlowUiEditorSwitcherWidget( config ) {
		var widget = this;
		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorSwitcherWidget.parent.call( this, config );

		// Mixin constructors
		OO.ui.mixin.PendingElement.call( this, config );

		this.placeholder = config.placeholder || '';
		this.initialContent = config.content || '';
		this.contentFormat = config.contentFormat || 'wikitext';
		this.activeEditorName = null;
		this.editors = {};
		this.availableEditors = mw.config.get( 'wgFlowEditorList' )
			.map( function ( editor ) {
				// Map 'none' to 'wikitext' for backwards compatibility
				return editor === 'none' ? 'wikitext' : editor;
			} )
			.filter( function ( editor ) {
				return widget.constructor.static.editorDefinitions[editor].static.isSupported();
			} );
		this.initialEditorName =
			( config.initialEditor === 'none' ? 'wikitext' : config.initialEditor ) ||
			this.getInitialEditorName();
		this.switchingPromise = null;

		this.triggerInput = new OO.ui.TextInputWidget( {
			multiline: false,
			placeholder: this.placeholder,
			classes: [ 'flow-ui-editorSwitcherWidget-trigger-input' ]
		} );

		// FIXME move trigger somewhere else
		this.triggerInput.$input
			.on( 'focusin', this.onTriggerInputFocusIn.bind( this ) );

		this.$element
			.on( 'focusin', this.onEditorFocusIn.bind( this ) )
			.on( 'focusout', this.onEditorFocusOut.bind( this ) );

		this.toggleCollapsed( !config.expanded );

		// Initialize
		this.$element
			.append( this.triggerInput.$element )
			.addClass( 'flow-ui-editorSwitcherWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorSwitcherWidget, OO.ui.Widget );
	OO.mixinClass( mw.flow.ui.EditorSwitcherWidget, OO.ui.mixin.PendingElement );

	/* Events */

	/**
	 * @event switch
	 *
	 * A `switch` event is emitted when the widget switches between different editors.
	 *
	 * @param {jQuery.Promise} switched Promise that is resolved when the switch is complete,
	 *   or rejected if the switch was aborted with an error.
	 * @param {string} newEditorName Name of the editor the widget is switching to
	 * @param {mw.flow.ui.AbstractEditorWidget|null} oldEditor Editor instance we're switching away from
	 */

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
	 * @return {mw.flow.ui.AbstractEditorWidget|null} Active editor, if any
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getActiveEditor = function () {
		return this.activeEditorName && this.editors[this.activeEditorName] || null;
	};

	/**
	 * Get the next editor after the currently active one.
	 * @return {string} Name of the next editor
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.getNextEditorName = function () {
		return this.availableEditors[
			( this.availableEditors.indexOf( this.activeEditorName ) + 1 ) %
			this.availableEditors.length
		];
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.getInitialEditorName = function () {
		var i, len;
		if ( this.initialContent !== '' ) {
			// Find the first editor that matches this.contentFormat
			for ( i = 0, len = this.availableEditors.length; i < len; i++ ) {
				if ( this.constructor.static.editorDefinitions[this.availableEditors[i]].static.format === this.contentFormat ) {
					return this.availableEditors[i];
				}
			}
		}
		// Either we didn't find an editor matching this.contentFormat, or we don't have any
		// initial content and so we don't care. Use the first editor.
		return this.availableEditors[0];
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
				'switch': 'onEditorSwitch',
				change: [ 'onEditorChange', this.editors[name] ]
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
	 * @return {jQuery.Promise} Promise resolved when editor switch is done
	 * @fires switch
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.switchEditor = function ( name ) {
		if ( this.switchingPromise ) {
			return this.switchingPromise;
		}

		var promise,
			stillSwitching = true,
			oldEditor = this.getActiveEditor(),
			oldContent = oldEditor && oldEditor.getContent() || this.initialContent,
			oldFormat = this.contentFormat,
			newEditor = this.getEditor( name ),
			newFormat = newEditor.getFormat(),
			widget = this;

		this.pushPending();
		if ( oldEditor ) {
			oldEditor.setDisabled( true );
		}
		this.activeEditorName = null;
		promise = this.convertContent( oldContent, oldFormat, newFormat )
			.then( function ( newContent ) {
				// Set up the new editor, but keep the old editor visible
				return newEditor.setup( newContent );
			} )
			.then( function () {
				// Hide and detach the current editor if it exists
				if ( oldEditor ) {
					oldEditor.toggle( false );
					return oldEditor.teardown()
						.then( function () {
							oldEditor.$element.detach();
						} );
				}
			} )
			.then( function () {
				widget.activeEditorName = name;
				widget.contentFormat = newFormat;
				widget.switchingPromise = null;
				stillSwitching = false;
				widget.popPending();
				newEditor.toggle( !widget.isCollapsed() );
				newEditor.setDisabled( widget.isDisabled() );
				widget.$element.append( newEditor.$element );

				if ( !widget.isCollapsed() ) {
					newEditor.focus();
				}

			} );

		this.emit( 'switch', promise, name, oldEditor );
		// It's possible that the .then() chain above ran completely synchronously, and the
		// switch is already complete. In that case, we shouldn't set this.switchingPromise,
		// because it will never be blanked.
		if ( stillSwitching ) {
			this.switchingPromise = promise;
		}
		return promise;
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
		if ( fromFormat === toFormat || content === '' ) {
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
	 * Respond to change events from an editor
	 *
	 * @private
	 * @param {mw.flow.ui.AbstractEditorWidget} editor Editor that fired the change event
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorChange = function ( editor ) {
		var name = editor.getName(),
			currentPref = mw.user.options.get( 'flow-editor' );
		if ( name !== this.activeEditorName ) {
			// Ignore change events from non-active editors
			return;
		}

		// Normalize 'none' to 'wikitext'
		if ( currentPref === 'none' ) {
			currentPref = 'wikitext';
		}

		if ( currentPref !== name ) {
			new mw.Api().saveOption( 'flow-editor', name );
			// Ensure we also see that preference in the current page
			mw.user.options.set( 'flow-editor', name );
		}
	};

	/**
	 * Respond to editor switch event. Switch either to the requested editor or
	 * to the next editor available.
	 *
	 * @private
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

	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusIn = function () {
		this.$element.addClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusOut = function () {
		this.$element.removeClass( 'flow-ui-editorSwitcherWidget-focused' );
	};
	mw.flow.ui.EditorSwitcherWidget.prototype.onTriggerInputFocusIn = function () {
		var widget = this;
		this.triggerInput.pushPending(); //TODO unnecessary?
		this.triggerInput.setDisabled( true );
		this.switchEditor( this.initialEditorName )
			.done( function () {
				widget.toggleCollapsed( false );
				widget.focus();
				widget.triggerInput.popPending();
				widget.triggerInput.setDisabled( false );
			} );
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
	 * then switching is not possible.
	 *
	 * @return {boolean} Editors are switchable
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.isSwitchable = function () {
		return this.availableEditors.length > 1;
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.isDisabled = function () {
		// Auto-disable when pending
		return this.isPending() ||
			// Parent method
			mw.flow.ui.EditorSwitcherWidget.parent.prototype.isDisabled.apply( this, arguments );
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.setDisabled = function ( disabled ) {
		var activeEditor = this.getActiveEditor();

		// Parent method
		mw.flow.ui.EditorSwitcherWidget.parent.prototype.setDisabled.call( this, disabled );

		if ( activeEditor ) {
			activeEditor.setDisabled( this.isDisabled() );
		}
		if ( this.triggerInput ) {
			this.triggerInput.setDisabled( this.isDisabled() );
		}
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.pushPending = function () {
		// Parent method
		OO.ui.mixin.PendingElement.prototype.pushPending.apply( this, arguments );

		// Disabled state depends on pending state
		this.updateDisabled();
	};

	mw.flow.ui.EditorSwitcherWidget.prototype.popPending = function () {
		// Parent method
		OO.ui.mixin.PendingElement.prototype.popPending.apply( this, arguments );

		// Disabled state depends on pending state
		this.updateDisabled();
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
