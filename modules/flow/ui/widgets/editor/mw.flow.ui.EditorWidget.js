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
	 * @cfg {boolean} [expanded=false] Set the widget's initial loading state to be
	 *  expanded. Otherwise, the widget will initialize in collapsed state
	 */
	mw.flow.ui.EditorWidget = function mwFlowUiEditorWidget( config ) {
		var defaultUserEditor = mw.user.options.get( 'flow-editor' );

		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorWidget.parent.call( this, config );

		// Mixin constructors
		OO.ui.mixin.PendingElement.call( this, config );

		this.editorControlsWidget = new mw.flow.ui.EditorControlsWidget( {
			termsMsgKey: config.termsMsgKey || 'flow-terms-of-use-edit',
			saveMsgKey: config.saveMsgKey || 'flow-newtopic-save',
			cancelMsgKey: config.cancelMsgKey || 'flow-cancel'
		} );

		this.editorSwitcherWidget = new mw.flow.ui.EditorSwitcherWidget( {
			initialEditor: config.editor || defaultUserEditor,
			content: config.content,
			contentFormat: config.contentFormat,
			placeholder: config.placeholder,
			expanded: !!config.expanded
		} );

		this.setPendingElement( this.editorSwitcherWidget.$element );

		// Events
		this.editorSwitcherWidget.connect( this, {
			collapsed: 'toggleCollapsed', // FIXME move trigger somewhere else
			'switch': 'onEditorSwitcherSwitch'
		} );
		this.editorControlsWidget.connect( this, {
			cancel: 'onEditorControlsWidgetCancel',
			save: 'onEditorControlsWidgetSave'
		} );

		this.$element
			.append(
				this.editorSwitcherWidget.$element,
				this.editorControlsWidget.$element
			)
			.addClass( 'flow-ui-editorWidget' );

		this.toggleCollapsed( !config.expanded );
	};

	/* Events */

	/**
	 * @event saveContent
	 * @param {string} content Content to save
	 * @param {string} contentFormat Format of content
	 */

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );
	OO.mixinClass( mw.flow.ui.EditorWidget, OO.ui.mixin.PendingElement );

	/**
	 * Make this widget pending while switching editors.
	 * @private
	 * @param {jQuery.Promise} promise Promise resolved/rejected when switch is completed/aborted
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorSwitcherSwitch = function ( promise ) {
		this.pushPending();
		promise.always( this.popPending.bind( this ) );
	};

	/**
	 * Relay save event
	 * @private
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsWidgetSave = function () {
		this.emit(
			'saveContent',
			this.editorSwitcherWidget.getActiveEditor().getContent(),
			this.editorSwitcherWidget.getActiveEditor().getFormat()
		);
	};

	/**
	 * Respond to cancel event
	 * @private
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsWidgetCancel = function () {
		this.toggleCollapsed( true );
	};

	/**
	 * Toggle collapsed state of the widget
	 *
	 * @param {boolean} [collapsed] Widget is collapsed
	 */
	mw.flow.ui.EditorWidget.prototype.toggleCollapsed = function ( collapsed ) {
		collapsed = collapsed !== undefined ? collapsed : !this.collapsed;
		if ( this.collapsed !== collapsed ) {
			this.collapsed = collapsed;
			this.editorSwitcherWidget.toggleCollapsed( this.collapsed );
			this.$element
				.toggleClass( 'flow-ui-editorWidget-expanded', !this.collapsed )
				.toggleClass( 'flow-ui-editorWidget-collapsed', this.collapsed );
		}
	};

	/**
	 * Check whether the widget is collapsed
	 * @return {boolean} Widget is collapsed
	 */
	mw.flow.ui.EditorWidget.prototype.isCollapsed = function () {
		return this.collapsed;
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
	 * Destroy the widget.
	 */
	mw.flow.ui.EditorWidget.prototype.destroy = function () {
		this.editorSwitcherWidget.destroy();
	};
}( jQuery ) );
