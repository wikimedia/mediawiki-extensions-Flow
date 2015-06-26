( function ( $ ) {
	/**
	 * Flow editor widget
	 *
	 * @class
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

		this.editorControlsWidget = new mw.flow.ui.EditorControlsWidget( {
			termsMsgKey: config.termsMsgKey || 'flow-terms-of-use-edit'
		} );

		this.editorSwitcherWidget = new mw.flow.ui.EditorSwitcherWidget( {
			placeholder: config.placeholder,
			expanded: !!config.expanded
		} );

		// Events
		this.editorSwitcherWidget.connect( this, {
			collapsed: 'toggleCollapsed'
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
		//TODO
		//this.editorSwitcherWidget.setEditorByKey( config.editor || defaultUserEditor );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.EditorWidget, OO.ui.Widget );

	/**
	 * Respond to save event
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsWidgetSave = function () {
		console.log( 'save' );
	};

	/**
	 * Respond to cancel event
	 */
	mw.flow.ui.EditorWidget.prototype.onEditorControlsWidgetCancel = function () {
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
}( jQuery ) );
