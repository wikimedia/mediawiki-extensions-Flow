( function ( $ ) {
	/**
	 * Flow visualeditor editor widget
	 *
	 * @extends OO.ui.Widget
	 *
	 * @constructor
	 * @param {Object} [config] Configuration options
	 * @cfg {string} [content] An initial content for the textarea
	 */
	mw.flow.ui.VisualEditorWidget = function mwFlowUiVisualEditorWidget( config ) {
		config = config || {};

		// Parent constructor
		mw.flow.ui.VisualEditorWidget.parent.call( this, config );
	};

	/* Initialization */

	OO.inheritClass( mw.flow.ui.VisualEditorWidget, OO.ui.AbstractEditorWidget );

	/* Static Methods */

	/**
	 * @inheritdoc
	 */
	mw.flow.ui.VisualEditorWidget.static.isSupported = function () {
		var isMobileTarget = ( mw.config.get( 'skin' ) === 'minerva' );

		return !!(
			!isMobileTarget &&

			// ES5 support, from es5-skip.js
			( function () {
				// This test is based on 'use strict',
				// which is inherited from the top-level function.
				return !this && !!Function.prototype.bind;
			}() ) &&

			// Since VE commit e2fab2f1ebf2a28f18b8ead08c478c4fc95cd64e, SVG is required
			document.createElementNS &&
			document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' ).createSVGRect &&

			// ve needs to be turned on as a valid editor
			mw.config.get( 'wgFlowEditorList' ).indexOf( 'visualeditor' ) !== -1
		);
	};
}( jQuery ) );
