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
		this.settingPromise = null;

		this.placeholderInput = new OO.ui.TextInputWidget( {
			multiline: true,
			placeholder: this.placeholder,
			classes: [ 'flow-ui-editorSwitcherWidget-placeholder-input' ]
		} );
		// The placeholder input is always pending, because it represnts
		// a loading state
		this.placeholderInput.pushPending();
		this.placeholderInput.setDisabled( true );

		this.error = new OO.ui.LabelWidget( {
			classes: [ 'flow-ui-editorSwitcherWidget-error flow-errors errorbox' ]
		} );
		this.error.toggle( false );

		this.$element
			.on( 'focusin', this.onEditorFocusIn.bind( this ) )
			.on( 'focusout', this.onEditorFocusOut.bind( this ) );

		// Initialize
		this.$element
			.append( this.error.$element, this.placeholderInput.$element )
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
	 * Activate the switcher with the chosen editor.
	 *
	 * @return {jQuery.Promise} Promise resolved when editor switch is done
	 * @fires switch
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.activate = function () {
		return this.switchEditor( this.initialEditorName );
	};

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
	 * @return {mw.flow.ui.AbstractEditorWidget} Editor instance
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
		if ( this.availableEditors.indexOf( name ) === -1 ) {
			return $.Deferred().reject().promise();
		}

		if ( this.switchingPromise ) {
			if ( this.switchingPromise.newEditorName === name ) {
				return this.switchingPromise;
			} else {
				// TODO handle this more gracefully
				return $.Deferred().reject().promise();
			}
		}

		if ( this.settingPromise ) {
			// TODO handle this more gracefully
			return $.Deferred().reject().promise();
		}

		var promise,
			switchingDeferred = $.Deferred(),
			oldName = this.activeEditorName,
			oldEditor = this.getActiveEditor(),
			oldContent = oldEditor && oldEditor.getContent() || this.initialContent,
			oldFormat = this.contentFormat,
			newEditor = this.getEditor( name ),
			newFormat = newEditor.getFormat(),
			widget = this;

		this.switchingPromise = promise = switchingDeferred.promise();
		this.switchingPromise.newEditorName = name;

		this.pushPending();
		this.error.toggle( false );
		if ( oldEditor ) {
			oldEditor.setDisabled( true );
		}
		this.activeEditorName = null;
		this.convertContent( oldContent, oldFormat, newFormat )
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
				switchingDeferred.resolve();
				widget.switchingPromise = null;

				widget.popPending();
				newEditor.setDisabled( widget.isDisabled() );

				widget.placeholderInput.$element.detach();
				widget.$element.append( newEditor.$element );

				newEditor.toggle( true );
				newEditor.focus();
			} )
			.fail( function ( error ) {
				widget.activeEditorName = oldName;
				switchingDeferred.reject();
				widget.switchingPromise = null;
				widget.popPending();
				if ( oldEditor ) {
					oldEditor.setDisabled( widget.isDisabled() );
				}

				widget.error.setLabel( $( '<span>' ).text( error || mw.msg( 'flow-error-default' ) ) );
				widget.error.toggle( true );
			} );

		this.emit( 'switch', promise, name, oldEditor );
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
				return mw.msg( 'flow-error-parsoid-failure' );
			} );
	};

	/**
	 * Change the content in the editor.
	 *
	 * If there is an active editor, the provided content will be converted to its format
	 * and then be put into that editor.
	 *
	 * If an editor switch is in progress, this method will
	 * wait for that switch to complete, then convert the provided content to the new editor's
	 * format and put the content into the new editor.
	 *
	 * If no editor has been loaded yet, this method will cause the provided content to be used
	 * when the first editor is loaded.
	 *
	 * @param {string} content New content to set
	 * @param {string} contentFormat Format of new content
	 * @return {jQuery.Promise} Promise resolved when new content has been set
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.setContent = function ( content, contentFormat ) {
		if ( this.settingPromise ) {
			// TODO handle this more gracefully
			return $.Deferred().reject();
		}

		if ( !this.switchingPromise && !this.getActiveEditor() ) {
			// There is no active editor, and no editor is loading, so we can simply change
			// our state variables and they'll be picked up when the first editor is loaded.
			this.initialContent = content;
			this.contentFormat = contentFormat;
			return $.Deferred().resolve().promise();
		}

		var promise,
			settingDeferred = $.Deferred(),
			widget = this;

		// Setting this.settingPromise prevents switchEditor() from changing the active editor
		// while we convert
		this.settingPromise = promise = settingDeferred.promise();
		$.when( this.switchingPromise )
			.then( function () {
				return widget.convertContent( content, contentFormat, widget.contentFormat );
			} )
			.then( function ( newContent ) {
				widget.getActiveEditor().setContent( newContent );
				settingDeferred.resolve();
				widget.settingPromise = null;
			} )
			.fail( function ( error ) {
				settingDeferred.reject();
				widget.settingPromise = null;

				widget.error.setLabel( $( '<span>' ).text( error || mw.msg( 'flow-error-default' ) ) );
				widget.error.toggle( true );
			} );
		return promise;
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
		if ( !name ) {
			name = this.getNextEditorName();
		}
		this.switchEditor( name );
	};

	/**
	 * Respond to focusin event
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusIn = function () {
		this.$element.addClass( 'flow-ui-editorSwitcherWidget-focused' );
	};

	/**
	 * Respond to focusout event
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.onEditorFocusOut = function () {
		this.$element.removeClass( 'flow-ui-editorSwitcherWidget-focused' );
	};

	/**
	 * Focus on the editor.
	 */
	mw.flow.ui.EditorSwitcherWidget.prototype.focus = function () {
		if ( this.getActiveEditor() ) {
			this.getActiveEditor().focus();
		}
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
