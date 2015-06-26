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
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.AbstractEditorWidget, OO.ui.Widget );

	/* Static Methods */

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
	 * Reload content based on API response into the editor
	 *
	 * @abstract
	 * @return {jQuery.Promise} Promise that resolves when the editor finished
	 *  loading the data. Promise is rejected if there was a problem loading
	 *  the data.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.reloadContent = null;

	/**
	 * Get the content of the editor
	 *
	 * @abstract
	 * @return {string} Content of the editor
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.getContent = null;

	/**
	 * Destroy the editor widget.
	 */
	mw.flow.ui.AbstractEditorWidget.prototype.destroy = null;
}( jQuery ) );
