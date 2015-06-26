( function ( $ ) {
	/**
	 * Flow editor widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [content] An initial content for the textarea
	 * @cfg {string} [placeholder] A placeholder for the base editor
	 * @cfg {string} [termsMsgKey='flow-terms-of-use-edit'] A i18n message key
	 *  for the footer message.
	 * @cfg {string} [editor] The initial editor to load. Defaults to the editor
	 *  set to the user preferences.
	 * @cfg {boolean} [expanded=false] Set the widget's initial loading state to be
	 *  expanded. Otherwise ,the widget will initialize in collapsed state
	 */
	mw.flow.ui.EditorWidget = function mwFlowUiEditorWidget( config ) {
		var defaultUserEditor = mw.user.options.get( 'flow-editor' );

		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorWidget.parent.call( this, config );

		this.editorControls = new mw.flow.ui.EditorControlsWidget( {
			termsMsgKey: config.termsMsgKey || 'flow-terms-of-use-edit'
		} );

		this.editorSwitcherWidget = new mw.flow.ui.EditorSwitcherWidget( {
			placeholder: config.placeholder,
			expanded: !!config.expanded
		} );

		// Events
		this.editorSwitcherWidget.connect( this, {
			collapsed: 'toggleCollapsed',
			editorSwitch: 'onEditorSwitch'
		} );
		this.editorControls.connect( this, {
			cancel: 'onEditorControlsCancel',
			save: 'onEditorControlsSave'
		} );

		this.$element
			.append(
				this.editorSwitcherWidget.$element,
				this.editorControls.$element
			)
			.addClass( 'flow-ui-editorWidget' );

		this.toggleCollapsed( !config.expanded );
		this.togglePending( false );
		this.editorSwitcherWidget.setEditorByKey( config.editor || defaultUserEditor );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );

	mw.flow.ui.EditorWidget.prototype.onEditorSwitch = function ( isOngoing ) {
		this.togglePending( isOngoing );
	};

	/**
	 * Respond to save event
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsSave = function () {
		console.log( 'save' );
	};

	/**
	 * Respond to cancel event
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsCancel = function () {
		this.toggleCollapsed( true );
	};

	/**
	 * Update user preference to a new editor
	 *
	 * @param {string} editorKey The key of the requested editor
	 */
	mw.flow.ui.EditorWidget.prototype.updateUserPreference = function ( editorKey ) {
		if ( mw.user.options.get( 'flow-editor' ) !== editorKey ) {
			new mw.Api().saveOption( 'flow-editor', editorKey );
			// ensure we also see that preference in the current page
			mw.user.options.set( 'flow-editor', editorKey );
		}
	};

	/**
	 * Toggle the pending state of the widget
	 *
	 * @param {boolean} [pending] Widget is pending
	 */
	mw.flow.ui.EditorWidget.prototype.togglePending = function ( pending ) {
		pending = pending !== undefined ? pending : !this.pending;

		if ( this.pending !== pending ) {
			this.pending = pending;

			this.$element.toggleClass( 'oo-ui-texture-pending', this.pending );
		}
	};

	/**
	 * Toggle collapsed state of the widget
	 *
	 * @param {boolean} collapsed Widget is collapsed
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
}( jQuery ) );
