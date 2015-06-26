//TODO rename me
( function ( $ ) {
	/**
	 * Flow abstract editor widget.
	 *
	 * Subclasses must:
	 * - Set #static-name
	 * - Implement #getRawContent and #reloadContent
	 * - Emit #event-change, #event-switch, #event-focusin and #event-focusout
	 * - Respect config.switchable
	 *
	 * Subclasses probably need to:
	 * - Set #static-format
	 * - Implement #static-isSupported
	 * - Implement #destroy
	 * - Respect config.placeholder
	 *
	 * @extends OO.ui.Widget
	 * @abstract
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {boolean} [switchable=false] Whether a tool to switch editors should be displayed
	 * @cfg {string} [placeholder] Placeholder text for the edit area
	 */
	mw.flow.ui.AbstractEditorWidget = function mwFlowUiAbstractEditorWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.AbstractEditorWidget.parent.call( this, config );

		this.togglePending( false );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.AbstractEditorWidget, OO.ui.Widget );

	/* Events */

	/**
	 * @event change
	 * Fired when the contents of the editor change.
	 */

	/**
	 * @event focusin
	 * Fired when the focus enters the editor.
	 */

	/**
	 * @event focusout
	 * Fired when the focus leaves the editor.
	 */

	/**
	  * @event switch
	  * @param {string} [editor] Symbolic name of the specific editor to switch to.
	  *
	  * Fired when the user expresses a desire to switch editors. If no specific editor
	  * name is passed in, or if there is no editor by the given name, EditorSwitcherWidget
	  * will decide which editor to switch to.
	  *
	  * Should not be emitted if config.switchable is false.
	  */

	/* Static Methods */

	/**
	 * Type of content to use
	 *
	 * @var {string}
	 */
	mw.flow.ui.AbstractEditorWidget.static.format = null;

	/**
	 * Name of this editor
	 *
	 * @var string
	 */
	mw.flow.ui.AbstractEditorWidget.static.name = null;

	/**
	 * Check if the editor is supported
	 *
	 * @return {boolean} Editor is supported
	 */
	mw.flow.ui.AbstractEditorWidget.static.isSupported = function () {
		return true;
	};

	/* Methods */

	/**
	 * Load the editor. Only called once, after construction.
	 * After the returned promise is resolved, #setup will be called, then
	 * the editor will be attached to the DOM.
	 *
	 * @return {jQuery.Promise} Promise that resolves once the editor
	 * is fully initialized.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.load = function () {
		return $.Deferred().resolve().promise();
	};

	/**
	 * Set up the editor.
	 *
	 * @method
	 * @abstract
	 * @param {string} content Content
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.setup = null;

	/**
	 * Get the content of the editor
	 *
	 * @abstract
	 * @method
	 * @return {string} Content of the editor
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.getContent = null;

	/**
	 * Focus on the editor.
	 *
	 * @method
	 * @abstract
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.focus = null;

	/**
	 * Get the format of the content in this editor
	 *
	 * @return {string} Content format
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.getFormat = function () {
		return this.constructor.static.format;
	};

	/**
	 * Get the name of this editor
	 *
	 * @return {string} Editor name
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.getName = function () {
		return this.constructor.static.name;
	};

	/**
	 * Toggle the pending state of the editor
	 *
	 * @param {boolean} [pending] Widget is pending
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.togglePending = function ( pending ) {
		pending = pending !== undefined ? pending : !this.pending;

		if ( this.pending !== pending ) {
			this.pending = pending;
			this.$element.toggleClass( 'oo-ui-texture-pending', this.pending );
		}
	};

	/**
	 * Destroy the editor widget.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.destroy = function () {
	};
}( jQuery ) );
