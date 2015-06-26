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
			placeholder: config.placeholder
		} );
		this.editorSwitcherWidget.setEditor( config.editor || defaultUserEditor );

		// Events
		this.editorSwitcherWidget.connect( this, {
			collapsed: 'toggleCollapsed'
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
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );

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
		console.log( 'cancel' );
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
			this.$element.toggleClass( 'flow-ui-editorWidget-expanded', !this.collapsed );
			this.$element.toggleClass( 'flow-ui-editorWidget-collapsed', this.collapsed );
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
