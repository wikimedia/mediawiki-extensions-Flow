( function ( $ ) {
	/**
	 * Flow editor widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [content] An initial content for the textarea
	 * @cfg {string} [termsMsgKey='flow-terms-of-use-edit'] A i18n message key
	 *  for the footer message.
	 * @cfg {string} [editor] The initial editor to load. Defaults to the editor
	 *  set to the user preferences.
	 */
	mw.flow.ui.EditorWidget = function mwFlowUiEditorWidget( config ) {
		var defaultUserEditor = mw.user.options.get( 'flow-editor' );

		config = config || {};

		// Parent constructor
		mw.flow.ui.EditorWidget.parent.call( this, config );

		this.editorControls = new mw.flow.ui.EditorControlsWidget( {
			termsMsgKey: config.termsMsgKey || 'flow-terms-of-use-edit'
		} );

		// Events
		this.editorControls.connect( this, {
			cancel: 'onEditorControlsCancel',
			save: 'onEditorControlsSave'
		} );

		this.editorSwitcherWidget = new mw.flow.ui.editorSwitcherWidget();
		this.editorSwitcherWidget.setEditor( config.editor || defaultUserEditor );

		// Editor wrapper
		this.$wrapper = $( '<div>' )
			.addClass( 'flow-ui-editorWidget-wrapper' );

		this.$element
			.append(
				this.$wrapper,
				this.editorControls
			)
			.addClass( 'flow-ui-editorWidget' );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );

	mw.flow.ui.EditorWidget.prototype.onEditorControlsSave = function () {
	};

	mw.flow.ui.EditorWidget.prototype.onEditorControlsCancel = function () {
	};
}( jQuery ) );
