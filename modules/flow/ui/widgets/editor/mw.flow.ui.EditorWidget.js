( function ( $ ) {
	/**
	 * Flow editor widget
	 *
	 * @class
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [editor] The initial editor to load. Defaults to the editor
	 *  set in the user's preferences.
	 * @cfg {string} [content] An initial content for the textarea
	 * @cfg {string} [contentFormat] Format of config.content
	 * @cfg {string} [placeholder] Placeholder text to use for the editor when empty
	 * @cfg {string} [termsMsgKey='flow-terms-of-use-edit'] i18n message key for the footer message
	 * @cfg {string} [saveMsgKey='flow-newtopic-save'] i18n message key for the save button
	 * @cfg {string} [cancelMsgKey='flow-cancel'] i18n message key for the cancel button
	 * @cfg {boolean} [autoFocus=true] Automatically focus after switching editors
	 * @cfg {boolean} [cancelOnEscape=true] Emit 'cancel' when Esc is pressed
	 * @cfg {boolean} [confirmCancel=true] Pop up a confirmation dialog if the user attempts
	 *  to cancel when there are changes in the editor.
	 */
	mw.flow.ui.EditorWidget = function mwFlowUiEditorWidget( config ) {
		var widget = this;

		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorWidget.parent.call( this, config );

		// Mixin constructors
		OO.ui.mixin.PendingElement.call( this, config );

		this.initialEditor = config.editor;
		this.confirmCancel = !!config.confirmCancel || config.cancelOnEscape === undefined;

		this.editorControlsWidget = new mw.flow.ui.EditorControlsWidget( {
			termsMsgKey: config.termsMsgKey || 'flow-terms-of-use-edit',
			saveMsgKey: config.saveMsgKey || 'flow-newtopic-save',
			cancelMsgKey: config.cancelMsgKey || 'flow-cancel'
		} );

		this.editorSwitcherWidget = new mw.flow.ui.EditorSwitcherWidget( {
			autoFocus: config.autoFocus,
			content: config.content,
			contentFormat: config.contentFormat,
			placeholder: config.placeholder
		} );

		this.setPendingElement( this.editorSwitcherWidget.$element );

		// Events
		this.editorSwitcherWidget.connect( this, {
			'switch': 'onEditorSwitcherSwitch',
			change: [ 'emit', 'change' ]
		} );
		this.editorControlsWidget.connect( this, {
			cancel: 'onEditorControlsWidgetCancel',
			save: 'onEditorControlsWidgetSave'
		} );

		if ( config.cancelOnEscape || config.cancelOnEscape === undefined ) {
			this.$element.on( 'keydown', function ( e ) {
				if ( e.which === OO.ui.Keys.ESCAPE ) {
					widget.onEditorControlsWidgetCancel();
					e.preventDefault();
					e.stopPropagation();
				}
			} );
		}

		this.$element
			.append(
				this.editorSwitcherWidget.$element,
				this.editorControlsWidget.$element
			)
			.addClass( 'flow-ui-editorWidget' );
	};

	/* Events */

	/**
	 * @event saveContent
	 * @param {string} content Content to save
	 * @param {string} contentFormat Format of content
	 */

	/**
	 * @event cancel
	 * The user clicked the cancel button.
	 */

	/**
	 * @event change
	 * The contents of the editor changed.
	 */

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

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );
	OO.mixinClass( mw.flow.ui.EditorWidget, OO.ui.mixin.PendingElement );

	// TODO figure out a way not to have to wrap so many things in EditorSwitcherWidget; maybe
	// merge EditorSwitcherWidget into EditorWidget?

	/**
	 * Respond to cancel event. Verify with the user that they want to cancel if
	 * there is changed data in the editor.
	 * @fires cancel
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsWidgetCancel = function () {
		var widget = this;

		if ( this.confirmCancel && this.editorSwitcherWidget.hasBeenChanged() ) {
			mw.flow.ui.windowManager.openWindow( 'cancelconfirm' )
				.then( function ( opened ) {
					opened.then( function ( closing, data ) {
						if ( data && data.action === 'discard' ) {
							// Remove content
							widget.setContent( '', 'wikitext' );
							widget.emit( 'cancel' );
						}
					} );
				} );
		} else {
			this.emit( 'cancel' );
		}
	};

	/**
	 * Check whether an editor is currently active. This returns false before the first editor
	 * is loaded, and true after that. It also returns true if and editor switch is in progress.
	 * @return {boolean} Editor is active
	 */
	mw.flow.ui.EditorWidget.prototype.isActive = function () {
		return this.editorSwitcherWidget.isActive();
	};

	/**
	 * Get the content of the editor.
	 * @return {string|null} Content of the editor, or null if no editor is active.
	 */
	mw.flow.ui.EditorWidget.prototype.getContent = function () {
		return this.editorSwitcherWidget.getContent();
	};

	/**
	 * Get the format of the content returned by #getContent.
	 * @return {string|null} Content format, or null if no editor is active.
	 */
	mw.flow.ui.EditorWidget.prototype.getContentFormat = function () {
		return this.editorSwitcherWidget.getContentFormat();
	};

	/**
	 * Check whether the editor is empty. If no editor is active, that is also considered empty.
	 * @return {boolean} Editor is empty
	 */
	mw.flow.ui.EditorWidget.prototype.isEmpty = function () {
		return this.editorSwitcherWidget.isEmpty();
	};

	/**
	 * Get the name of the editor that would be loaded if this widget were to be
	 * activated right now.
	 *
	 * Note that the return value of this function can change over time as the user switches
	 * editors in different EditorWidgets. The editor that is actually loaded when activating
	 * is determined by calling this function at activation time, no earlier.
	 *
	 * @return {string} Name of initial editor that will be used
	 */
	mw.flow.ui.EditorWidget.prototype.getInitialEditorName = function () {
		return this.initialEditor || mw.user.options.get( 'flow-editor' );
	};

	/**
	 * Get the format of the editor that would be loaded if this widget were to be
	 * activated right now.
	 * @return {string|null} Format used by initial editor, or null if no editor is active
	 * @see #getInitialEditorName
	 */
	mw.flow.ui.EditorWidget.prototype.getInitialFormat = function () {
		return this.editorSwitcherWidget.getEditorFormat( this.getInitialEditorName() );
	};

	/**
	 * Toggle whether the editor is automatically focused after switching.
	 * @param {boolean} [autoFocus] Whether to focus automatically; if unset, flips current value
	 */
	mw.flow.ui.EditorWidget.prototype.toggleAutoFocus = function ( autoFocus ) {
		this.editorSwitcherWidget.toggleAutoFocus( autoFocus );
	};

	/**
	 * Change the content in the editor
	 * @param {string} content New content to set
	 * @param {string} contentFormat Format of new content
	 * @return {jQuery.Promise} Promise resolved when new content has been set
	 * @see mw.flow.ui.EditorSwitcherWidget#setContent
	 */
	mw.flow.ui.EditorWidget.prototype.setContent = function ( content, contentFormat ) {
		return this.editorSwitcherWidget.setContent( content, contentFormat );
	};

	/**
	 * Make this widget pending while switching editors.
	 * @private
	 * @param {jQuery.Promise} promise Promise resolved/rejected when switch is completed/aborted
	 * @fires switch
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorSwitcherSwitch = function ( promise ) {
		this.pushPending();
		promise.always( this.popPending.bind( this ) );
		this.emit.apply( this, [ 'switch' ].concat( Array.prototype.slice.apply( arguments ) ) );
	};

	/**
	 * Relay save event
	 * @private
	 * @fires saveContent
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsWidgetSave = function () {
		this.emit(
			'saveContent',
			this.editorSwitcherWidget.getActiveEditor().getContent(),
			this.editorSwitcherWidget.getActiveEditor().getFormat()
		);
	};

	/**
	 * Activate the first editor, if not already active.
	 * @return {jQuery.Promise} Promise resolved when editor switch is done
	 */
	mw.flow.ui.EditorWidget.prototype.activate = function () {
		if ( this.isActive() ) {
			return $.Deferred().resolve().promise();
		}

		// Doesn't call editorSwitcherWidget.activate() because we want to
		// evaluate the user preference as late as possible
		var editor = this.initialEditor || mw.user.options.get( 'flow-editor' );
		if ( editor === 'none' ) {
			editor = 'wikitext';
		}

		if ( this.editorSwitcherWidget.isEditorAvailable( editor ) ) {
			return this.editorSwitcherWidget.switchEditor( editor );
		}
		// If the editor we want isn't available, let EditorSwitcherWidget decide which editor to use
		return this.editorSwitcherWidget.activate();
	};

	mw.flow.ui.EditorWidget.prototype.isDisabled = function () {
		// Auto-disable when pending
		return this.isPending() ||
			// Parent method
			mw.flow.ui.EditorWidget.parent.prototype.isDisabled.apply( this, arguments );
	};

	mw.flow.ui.EditorWidget.prototype.setDisabled = function ( disabled ) {
		// Parent method
		mw.flow.ui.EditorWidget.parent.prototype.setDisabled.call( this, disabled );

		if ( this.editorSwitcherWidget && this.editorControlsWidget ) {
			this.editorSwitcherWidget.setDisabled( this.isDisabled() );
			this.editorControlsWidget.setDisabled( this.isDisabled() );
		}
	};

	mw.flow.ui.EditorWidget.prototype.pushPending = function () {
		// Parent method
		OO.ui.mixin.PendingElement.prototype.pushPending.apply( this, arguments );

		// Disabled state depends on pending state
		this.updateDisabled();
	};

	mw.flow.ui.EditorWidget.prototype.popPending = function () {
		// Parent method
		OO.ui.mixin.PendingElement.prototype.popPending.apply( this, arguments );

		// Disabled state depends on pending state
		this.updateDisabled();
	};

	/**
	 * Focus the current editor
	 */
	mw.flow.ui.EditorWidget.prototype.focus = function () {
		this.editorSwitcherWidget.focus();
	};

	/**
	 * Destroy the widget.
	 */
	mw.flow.ui.EditorWidget.prototype.destroy = function () {
		this.editorSwitcherWidget.destroy();
	};
}( jQuery ) );
