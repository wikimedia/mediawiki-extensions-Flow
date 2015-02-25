( function ( mw ) {
	'use strict';

	/**
	 * Abstract editor class for Flow content
	 * Sets certain defaults, but most have to be implemented in subclasses
	 *
	 * @class
	 * @abstract
	 *
	 * @constructor
	 */
	mw.flow.editor.AbstractEditor = function () {};

	OO.initClass( mw.flow.editor.AbstractEditor );

	// Static methods

	/**
	 * Returns whether this editor uses a preview mode
	 *
	 * @return {boolean}
	 */
	mw.flow.editor.AbstractEditor.static.usesPreview = function () {
		return true;
	};

	/**
	 * Determines if this editor is supported for the current user and
	 * environment (browser, etc.)
	 *
	 * @return {boolean}
	 */
	mw.flow.editor.AbstractEditor.static.isSupported = function () {
		return true;
	};
} ( mediaWiki ) );
