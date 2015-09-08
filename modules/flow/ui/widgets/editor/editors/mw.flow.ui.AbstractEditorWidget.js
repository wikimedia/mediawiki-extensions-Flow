( function ( $ ) {
	/**
	 * Flow abstract editor widget.
	 *
	 * Subclasses must:
	 * - Set #static-name
	 * - Implement #getRawContent and #reloadContent
	 * - Emit #event-change and #event-switch
	 * - Respect config.switchable
	 *
	 * Subclasses probably need to:
	 * - Set #static-format
	 * - Implement #static-isSupported
	 * - Implement #destroy
	 * - Respect config.placeholder
	 *
	 * @class
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

		this.initialContent = null;

		// Parent constructor
		mw.flow.ui.AbstractEditorWidget.parent.call( this, config );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.AbstractEditorWidget, OO.ui.Widget );

	/* Events */

	/**
	 * @event change
	 * Fired when the contents of the editor change.
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
	 * @static
	 * @inheritable
	 * @type {string}
	 */
	mw.flow.ui.AbstractEditorWidget.static.format = null;

	/**
	 * Name of this editor
	 *
	 * @static
	 * @inheritable
	 * @type {string}
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
	 * Set up the editor.
	 *
	 * @method
	 * @abstract
	 * @param {string} content Content
	 * @return {jQuery.Promise} Promise resolved when setup is done, or rejected if it failed,
	 *   optionally with an error message.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.setup = null;

	/**
	 * Called immediately after the editor is attached to the DOM and made visible.
	 * Use this method only for things that rely on the editor being attached and visible;
	 * put other things in #setup.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.afterAttach = function () {
	};

	/**
	 * Tear down the editor.
	 *
	 * @method
	 * @abstract
	 * @return {jQuery.Promise} Promise resolved when teardown is done, or rejected if it failed,
	 *   optionally with an error message.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.teardown = null;

	/**
	 * Get the content of the editor.
	 *
	 * @abstract
	 * @method
	 * @return {string} Content of the editor
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.getContent = null;

	/**
	 * Change the content of the editor.
	 *
	 * @method
	 * @param {string} content New content
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.setContent = function ( content ) {
		// Cache content for comparison
		this.initialContent = content;
	};

	/**
	 * Focus on the editor.
	 *
	 * @abstract
	 * @method
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.focus = null;

	/**
	 * Move the cursor to the end of the editor.
	 *
	 * @abstract
	 * @method
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.moveCursorToEnd = null;

	/**
	 * Destroy the editor widget.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.destroy = function () {
	};

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
	 * Check whether the editor is empty.
	 * @return {boolean} Editor is empty
	 * @localdoc Subclasses may override this with a more efficient implementation.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.isEmpty = function () {
		return !this.getContent();
	};

	/**
	 * Check if the information in the editor changed
	 * @return {boolean} Information has changed
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.hasBeenChanged = function () {
		return this.initialContent !== this.getContent();
	};

}( jQuery ) );
