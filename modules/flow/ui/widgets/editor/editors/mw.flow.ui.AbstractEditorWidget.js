( function ( $ ) {
	/**
	 * Flow abstract editor widget
	 *
	 * @extends OO.ui.Widget
	 * @abstract
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 */
	mw.flow.ui.AbstractEditorWidget = function mwFlowUiAbstractEditorWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.AbstractEditorWidget.parent.call( this, config );
		this.togglePending( false );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.AbstractEditorWidget, OO.ui.Widget );

	/* Static Methods */

	/**
	 * Type of content to use
	 *
	 * @var {string}
	 */
	mw.flow.ui.AbstractEditorWidget.static.format = 'plaintext';

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
	 * Initialize the editor.
	 * @return {jQuery.Promise} Promise that resolves once the editor
	 * is fully initialized.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.initialize = function () {
		return $.Deferred().resolve().promise();
	};

	/**
	 * Reload content based on API response into the editor
	 *
	 * @method
	 * @abstract
	 * @return {jQuery.Promise} Promise that resolves when the editor finished
	 *  loading the data. Promise is rejected if there was a problem loading
	 *  the data.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.reloadContent = null;

	/**
	 * Focus on the editor
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.focus = function () {
		this.$element.focus();
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
	 * Get the content of the editor
	 *
	 * @abstract
	 * @method
	 * @return {string} Content of the editor
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.getRawContent = null;

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
	 *
	 * @method
	 * @abstract
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.destroy = null;
}( jQuery ) );
